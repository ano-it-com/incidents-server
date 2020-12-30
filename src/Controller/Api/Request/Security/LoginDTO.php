<?php


namespace App\Controller\Api\Request\Security;


use Symfony\Component\Validator\Constraints as Assert;

class LoginDTO
{
    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotBlank(),
     */
    public $login;

    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotBlank(),
     */
    public $password;

}