<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UserRepository")
 */
class User extends BaseUser implements UserInterface
{

    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    private $facebook_id;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_access_token", type="string", nullable=true)
     */
    private $facebook_access_token;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Question", mappedBy="author")
     */
    private $questions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Answer", mappedBy="author")
     */
    private $answers;

    /**
     * @var integer
     *
     * @ORM\Column(name="karma", type="integer")
     */
    private $karma = 0;

    /**
     * @var Answer
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Answer", mappedBy="usersVoted")
     */
    private $votedAnswers;

    /**
     * Set facebookId
     *
     * @param string $facebookId
     *
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     *
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebook_access_token = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * Set karma
     *
     * @param integer $karma
     *
     * @return User
     */
    public function setKarma($karma)
    {
        $this->karma = $karma;

        return $this;
    }

    /**
     * Get karma
     *
     * @return integer
     */
    public function getKarma()
    {
        return $this->karma;
    }

    /**
     * Add question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return User
     */
    public function addQuestion(\AppBundle\Entity\Question $question)
    {
        $this->questions[] = $question;

        return $this;
    }

    /**
     * Remove question
     *
     * @param \AppBundle\Entity\Question $question
     */
    public function removeQuestion(\AppBundle\Entity\Question $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Add answer
     *
     * @param \AppBundle\Entity\Answer $answer
     *
     * @return User
     */
    public function addAnswer(\AppBundle\Entity\Answer $answer)
    {
        $this->answers[] = $answer;

        return $this;
    }

    /**
     * Remove answer
     *
     * @param \AppBundle\Entity\Answer $answer
     */
    public function removeAnswer(\AppBundle\Entity\Answer $answer)
    {
        $this->answers->removeElement($answer);
    }

    /**
     * Get answers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add votedAnswer
     *
     * @param \AppBundle\Entity\Answer $votedAnswer
     *
     * @return User
     */
    public function addVotedAnswer(\AppBundle\Entity\Answer $votedAnswer)
    {
        $this->votedAnswers[] = $votedAnswer;

        return $this;
    }

    /**
     * Remove votedAnswer
     *
     * @param \AppBundle\Entity\Answer $votedAnswer
     */
    public function removeVotedAnswer(\AppBundle\Entity\Answer $votedAnswer)
    {
        $this->votedAnswers->removeElement($votedAnswer);
    }

    /**
     * Get votedAnswers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVotedAnswers()
    {
        return $this->votedAnswers;
    }
}
