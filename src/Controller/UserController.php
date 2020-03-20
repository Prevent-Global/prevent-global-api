<?php


namespace App\Controller;


use App\Entities\UserInfo;
use App\Infra\UserInfoPersister;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{

    /**
     * @Route("/api/user/register", name="user_register")
     */
    public function register(Request $request, UserInfoPersister $userInfoPersister)
    {
        try {
            $requestContent = json_decode($request->getContent(), true);

            $recentTest = $requestContent['recentTest'];

            $userInfo = UserInfo::createNew($recentTest);

            $userInfoPersister->saveUser($userInfo);

            $response = new Response();
            $response->headers->set("Content-Type", "application/json");
            $response->setContent(json_encode($userInfo), Response::HTTP_OK);
            return $response;

        } catch (\InvalidArgumentException $e) {
            return new Response(null, Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}