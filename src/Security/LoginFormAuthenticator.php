<?php

namespace App\Security;

use App\Repository\RoleRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private $roleRepository;
    private $security;

    public function __construct(UrlGeneratorInterface $urlGenerator, RoleRepository $roleRepository, Security $security)
    {
        $this->urlGenerator = $urlGenerator;
        $this->roleRepository = $roleRepository;
        $this->security = $security;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($this->security->getUser()->getRoles()['0'] === 'ROLE_ADMIN') {
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }
        if ($this->security->getUser()->getRoles()['0'] === 'ROLE_READER') {
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }
        if ($this->security->getUser()->getRoles()['0'] === 'ROLE_REPRESENTANT') {
            return new RedirectResponse($this->urlGenerator->generate('app_resultat_index'));
        }
        if ($this->security->getUser()->getRoles()['0'] === 'ROLE_JOURNALISTE') {
            return new RedirectResponse($this->urlGenerator->generate('app_resultat_journaliste'));
        }
        
        // dd(($this->security->getUser()->getRoles()['0'] === 'ROLE_ADMIN'));
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            // dd($targetPath);
            return new RedirectResponse($targetPath);
        }
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
