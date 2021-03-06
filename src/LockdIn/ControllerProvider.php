<?php

namespace LockdIn;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class ControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/authorize', function (Application $app) {
            if (!$app['oauth_server']->validateAuthorizeRequest($app['request'])) {
                return $app['oauth_server']->getResponse();
            }
            return $app['twig']->render('lockdin/authorize.twig');
        })->bind('authorize');

        $controllers->post('/authorize', function (Application $app) {
            $authorized = (bool) $app['request']->request->get('authorize');
            return $app['oauth_server']->handleAuthorizeRequest($app['request'], $authorized);
        })->bind('authorize_post');

        $controllers->get('/grant', function(Application $app) {
            return $app['oauth_server']->handleGrantRequest($app['request']);
        })->bind('grant');

        $controllers->get('/access', function(Application $app) {
            $server = $app['oauth_server'];
            if (!$server->verifyAccessRequest($app['request'])) {
                return $server->getResponse();
            } else {
                return new Response(json_encode(array('friends' => array('john', 'matt', 'jane'))));
            }
        })->bind('access');

        return $controllers;
    }
}