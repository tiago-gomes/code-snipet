<?php

namespace App\Domain\Controller;

use App\Domain\Model\AccountModel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends Controller
{
    /**
     * @var AccountModel
     */
    protected $accountModel;

    /**
     * AccountController constructor.
     * @param AccountModel $AccountModel
     */
    public function __construct(AccountModel $AccountModel)
    {
        $this->accountModel = $AccountModel;
    }

    /**
     * @Route("/account", name="getAllAccounts", methods={"GET"})
     * @return JsonResponse
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function index() {
        $account = $this->accountModel->getAllAccounts();
        return new JsonResponse([
            'code' => 200,
            'data' => $account
        ]);
    }

    /**
     * Show account details by ID
     *
     * @Route("/account/{id}", requirements={"id"="\d+"}, name="getAccountById", methods={"GET"})
     * @param \Symfony\Bundle\FrameworkBundle\Controller\string $id
     * @return object|JsonResponse
     * @throws \Exception
     */
    public function getById($id) {
        $account = $this->accountModel->getAccountById($id);
        return new JsonResponse([
            'code' => 200,
            'data' => $account->toArray()
        ]);
    }

    /**
     * @Route("/account", requirements={"id"="\d+"}, name="addAccount", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function post(Request $request) {
        $account = $this->accountModel->addAccount($request->request->all());
        return new JsonResponse([
            'code' => 200,
            'data' => $account->toArray()
        ]);
    }

    /**
     * Update an existing account
     *
     * @Route("/account/{id}", requirements={"id"="\d+"}, name="updateAccount", methods={"PATCH"})
     * @param $id
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function patch($id, Request $request) {
        $account = $this->accountModel->updateAccount($id, $request->query->all());
        return new JsonResponse([
            'code' => 200,
            'data' => $account->toArray()
        ]);
    }

    /**
     * Remove an existing account
     *
     * @Route("/account/{id}", requirements={"id"="\d+"}, name="removeAccount", methods={"DELETE"})
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function delete($id) {
        $this->accountModel->removeAccount($id);
        return new JsonResponse([
            'code' => 200,
            'message' => 'Account was successfully removed.'
        ]);
    }
}
