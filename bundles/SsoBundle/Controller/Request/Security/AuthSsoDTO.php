<?php


namespace SsoBundle\Controller\Request\Security;

use Symfony\Component\Validator\Constraints as Assert;

class AuthSsoDTO
{
    /**
     * @var string
     * @Assert\Type("string")
     * @Assert\NotBlank(),
     */
    public $code;

}