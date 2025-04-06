<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Response;

#[ORM\Entity]
class Response_responses
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private int $id;

        #[ORM\ManyToOne(targetEntity: Response::class, inversedBy: "response_responsess")]
    #[ORM\JoinColumn(name: 'response_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Response $response_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $answer;

    #[ORM\Column(type: "string", length: 255)]
    private string $question;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getResponse_id()
    {
        return $this->response_id;
    }

    public function setResponse_id($value)
    {
        $this->response_id = $value;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function setAnswer($value)
    {
        $this->answer = $value;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function setQuestion($value)
    {
        $this->question = $value;
    }
}
