<?php
namespace App\Api\V1\Controllers;

use App\Models\Orders;
use App\Models\Settings;
use App\Payments\Paypal;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayPalController extends Controller
{

    /**
     * @var Orders
     */
    protected $ordersManager;

    /**
     * @var Settings
     */
    protected $settingsManager;

    /**
     * @var Paypal
     */
    protected $paypal;

    /**
     * PaypalController constructor & dependency injection
     * @param Orders $ordersManager
     * @param Paypal $paypal

     */
    public function __construct(Orders $ordersManager, Settings $settingsManager, Paypal $paypal)
    {
        $this->ordersManager    = $ordersManager;
        $this->settingsManager  = $settingsManager;
        $this->paypal           = $paypal;
    }

    /**
     * Saves the pre-payment state and returns the new row
     *
     * @param Request $request
     * @return mixed
     */
    public function post(Request $request)
    {
        $orders         = $this->ordersManager;
        $calculations   = $this->ordersManager->calculations($request->input());
        $data           = json_decode($calculations);

        $orders->user_id            = $request->user_id;
        $orders->customer_id        = $request->customer_id;
        $orders->availability_id    = $request->availability_id;
        $orders->currency           = $request->currency;

        $orders->calculations       = (string) $calculations;
        $orders->payment_amount     = (string) $data->calculations->grandTotal;
        $orders->payment_gateway   = 'Paypal';
        $orders->payment_status    = 'pending';
        $orders->currency          = 'USD';

        $orders->save();

        return response()->json(
            [
                'code'      =>  200,
                Orders::RESOURCE =>  $orders->find($orders->id)->toArray()
            ],
            200
        );
    }

    /**
     *
     * Confirmation Page and checkout
     *
     * @param int $order_id
     *
     * @return mixed
     */
    public function checkout($order_id)
    {
        $order = $this->ordersManager->findOrFail($order_id);

        $settings = $this->settingsManager->where("user_id", "=", $order->user_id)
            ->first();

        $this->paypal->setCredentials(
            $settings->nvpUsername,
            $settings->nvpPassword,
            $settings->nvpSignature,
            true
        );

        $response = $this->paypal->purchase([
            'amount' => $this->paypal->formatAmount($order->payment_amount),
            'transactionId' => 'TXT' .$order->id,
            'invoiceNumber' => 'IBK' . $order->id,
            'currency' => $settings->currency,
            'cancelUrl' => $this->paypal->getCancelUrl($order),
            'returnUrl' => $this->paypal->getReturnUrl($order)
        ]);

        if ($response->isRedirect()) {
            return response()->json(
                [
                    'code' => 200,
                    'redirect' => $response->getRedirectUrl()
                ],
                200
            );
        }

        return response()->json(
            [
                'code' => 401,
                'message' => $response->getMessage()
            ],
            401
        );
    }

    /**
     *
     * Sets the status to completed.
     *
     * @param $order_id
     * @param Request $request
     * @return mixed
     */
    public function completed($order_id, Request $request)
    {

        $order = $this->ordersManager->findOrFail($order_id);

        $settings = $this->settingsManager->where("user_id", "=", $order->user_id)
            ->first();

        $this->paypal->setCredentials(
            $settings->nvpUsername,
            $settings->nvpPassword,
            $settings->nvpSignature,
            true
        );

        $response = $this->paypal->complete([
            'amount' => $this->paypal->formatAmount($order->payment_amount),
            'transactionId' => 'TXT' .$order->id,
            'invoiceNumber' => 'BKM' . $order->id,
            'currency' => $settings->currency,
            'cancelUrl' => $this->paypal->getCancelUrl($order),
            'returnUrl' => $this->paypal->getReturnUrl($order),
            'notifyUrl' => $this->paypal->getNotifyUrl($order),
        ]);

        if ($response->isSuccessful()) {
            $order->update(
                [
                    'transaction_id' => $response->getTransactionReference(),
                    'payment_status' => 'completed'
                ]
            );

            // probably send an email to the client.

            return response()->json(
                [
                    'code' => 200,
                    'message' => 'The reference code for the payment is ' . $response->getTransactionReference()
                ],
                200
            );
        }
        return response()->json(
            [
                'code' => 401,
                'message' => $response->getMessage()
            ],
            401
        );
    }

    /**
     *
     * Issue a refund
     *
     * @param $order_id
     * @param Request $request
     * @return mixed
     */
    public function refund($order_id, Request $request)
    {

        $order = $this->ordersManager->findOrFail($order_id);

        $settings = $this->settingsManager->where("user_id", "=", $order->user_id)
            ->first();

        $this->paypal->setCredentials(
            $settings->nvpUsername,
            $settings->nvpPassword,
            $settings->nvpSignature,
            true
        );

        $response = $this->paypal->refund([
            'amount' => $this->paypal->formatAmount($order->payment_amount),
            'transactionReference' => $order->transaction_id,
            'cancelUrl' => $this->paypal->getCancelUrl($order),
            'returnUrl' => $this->paypal->getReturnUrl($order),
            'notifyUrl' => $this->paypal->getNotifyUrl($order)
        ]);

        if ($response->isSuccessful()) {
            $order->update([
                'refund_transaction_id' => $response->getTransactionReference(),
                'payment_status'        => 'refund'
            ]);

            // probably send an email to the client.

            return response()->json([
                    'code' => 200,
                    'message' => 'The reference code for the refund is ' . $response->getTransactionReference()
                ],
                200
            );
        }
        return response()->json(
            [
                'code' => 401,
                'message' => $response->getMessage()
            ],
            401
        );
    }

    /**
     *
     * Cancels the payment operation and issues an 100% refund.
     *
     * @param $order_id
     * @return mixed
     */
    public function cancelled($order_id)
    {

        $order = $this->ordersManager->findOrFail($order_id);

        $order->update([
                'payment_status' => 'cancelled'
         ]);

        return response()->json(
            [
                'code' => 200,
                'message' => 'You have cancelled your recent PayPal payment, but no Refund was made.'
            ],
            200
        );
    }

    /**
     *
     * Not yet specified.
     *
     * @param $order_id
     * @param $env
     */
    public function webhook($order_id, $env)
    {

    }
}