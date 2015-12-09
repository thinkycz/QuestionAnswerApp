<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Answer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\AnswerRepository")
 */
class Answer
{

    use TimestampableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="answers")
     */
    private $author;

    /**
     * @var integer
     *
     * @ORM\Column(name="useful", type="integer")
     */
    private $useful = 0;

    /**
     * @var User
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", inversedBy="votedAnswers")
     */
    private $usersVoted;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Question", inversedBy="answers")
     */
    private $question;

    /**
     * @var Answer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Answer", inversedBy="children")
     */
    private $parent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Answer", mappedBy="parent")
     */
    private $children;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Image", mappedBy="answer")
     */
    private $images;

    /**
     * @var Question
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Question", mappedBy="selectedAnswer")
     */
    private $isSelectedIn;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Answer
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set useful
     *
     * @param integer $useful
     *
     * @return Answer
     */
    public function setUseful($useful)
    {
        $this->useful = $useful;

        return $this;
    }

    /**
     * Get useful
     *
     * @return integer
     */
    public function getUseful()
    {
        return $this->useful;
    }

    /**
     * Set question
     *
     * @param \AppBundle\Entity\Question $question
     *
     * @return Answer
     */
    public function setQuestion(\AppBundle\Entity\Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return \AppBundle\Entity\Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set parent
     *
     * @param \AppBundle\Entity\Answer $parent
     *
     * @return Answer
     */
    public function setParent(\AppBundle\Entity\Answer $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\Answer
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\Answer $child
     *
     * @return Answer
     */
    public function addChild(\AppBundle\Entity\Answer $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\Answer $child
     */
    public function removeChild(\AppBundle\Entity\Answer $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add image
     *
     * @param \AppBundle\Entity\Image $image
     *
     * @return Answer
     */
    public function addImage(\AppBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \AppBundle\Entity\Image $image
     */
    public function removeImage(\AppBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set author
     *
     * @param \AppBundle\Entity\User $author
     *
     * @return Answer
     */
    public function setAuthor(\AppBundle\Entity\User $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Add usersVoted
     *
     * @param \AppBundle\Entity\User $usersVoted
     *
     * @return Answer
     */
    public function addUsersVoted(\AppBundle\Entity\User $usersVoted)
    {
        $this->usersVoted[] = $usersVoted;

        return $this;
    }

    /**
     * Remove usersVoted
     *
     * @param \AppBundle\Entity\User $usersVoted
     */
    public function removeUsersVoted(\AppBundle\Entity\User $usersVoted)
    {
        $this->usersVoted->removeElement($usersVoted);
    }

    /**
     * Get usersVoted
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsersVoted()
    {
        return $this->usersVoted;
    }

    /**
     * Set isSelectedIn
     *
     * @param \AppBundle\Entity\Question $isSelectedIn
     *
     * @return Answer
     */
    public function setIsSelectedIn(\AppBundle\Entity\Question $isSelectedIn = null)
    {
        $this->isSelectedIn = $isSelectedIn;

        return $this;
    }

    /**
     * Get isSelectedIn
     *
     * @return \AppBundle\Entity\Question
     */
    public function getIsSelectedIn()
    {
        return $this->isSelectedIn;
    }
}
