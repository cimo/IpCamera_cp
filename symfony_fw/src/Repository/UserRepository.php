<?php
namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface {
    // Vars
    
    // Properties
    
    // Functions public
    public function loadUserByUsername($username) {
        $user = $this->findByUsernameOrEmail($username);
        
        return $user;
    }
    
    // Functions private
    private function findByUsernameOrEmail($value) {
        return $this->createQueryBuilder("user")
            ->where("user.username = :username OR user.email = :email")
            ->setParameter("username", $value)
            ->setParameter("email", $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}