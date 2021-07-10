<?php


namespace App\Security;

use App\Entity\ApcRessource;
use App\Entity\ApcSae;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ReferentielVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `ApcRessource` or ApcRessource objects
        if (!($subject instanceof ApcRessource || $subject instanceof ApcSae)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // ROLE_GT can do anything! The power!
        if ($this->security->isGranted('ROLE_GT')) {
            return true;
        }

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        $post = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($post, $user);
            case self::EDIT:
                return $this->canEdit($post, $user);
            case self::DELETE:
                return $this->canDelete($post, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(ApcSae|ApcRessource $post, User $user): bool
    {
        // if they can edit, they can view
        if ($this->canEdit($post, $user)) {
            return true;
        }

        // the Post object could have, for example, a method `isPrivate()`
        //todo: ajouter champ blocage édition sur ??? département ??? tableaux ???
        return true;
    }

    private function canEdit(ApcSae|ApcRessource $post, User $user): bool
    {
        if ($this->security->isGranted('ROLE_LECTEUR')) {
            return false;
        }

        if ($user->getDepartement() === null || $post->getDepartement() === null) {
            return false;
        }
        // this assumes that the Post object has a `getOwner()` method
        return $user->getDepartement()->getId() === $post->getDepartement()->getId();
    }

    private function canDelete(ApcSae|ApcRessource $post, User $user): bool
    {
        if (!($this->security->isGranted('ROLE_PACD') || $this->security->isGranted('ROLE_CPN'))) {
            return false;
        }

        if ($user->getDepartement() === null || $post->getDepartement() === null) {
            return false;
        }
        // this assumes that the Post object has a `getOwner()` method
        return $user->getDepartement()->getId() === $post->getDepartement()->getId();
    }
}
