<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class Campaign
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Campaign
{
    /** @var string */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $fromField;

    /** @var string */
    private $replyTo;

    /** @var string */
    private $subject;

    /** @var string */
    private $body;

    /**
     * @param string $id
     * @param string $name
     * @param string $fromField
     * @param string $replyTo
     * @param string $subject
     * @param string $body
     */
    public function __construct($id, $name, $fromField, $replyTo, $subject, $body)
    {
        $this->id = $id;
        $this->name = $name;
        $this->fromField = $fromField;
        $this->replyTo = $replyTo;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFromField()
    {
        return $this->fromField;
    }

    /**
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }
}
