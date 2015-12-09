<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * QuestionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class QuestionRepository extends EntityRepository
{
    public function findQuestionByQueryString($queryString)
    {
        return $this->createQueryBuilder('question')
            ->select('question')
            ->where('question.title LIKE :string')
            ->setParameter('string', '%'.$queryString.'%')
            ->getQuery()->getResult();
    }

    public function count()
    {
        return $this->createQueryBuilder('question')
            ->select('count(question.id)')
            ->getQuery()->getSingleScalarResult();
    }

    public function getQuestionBySlug($slug)
    {
        $question = $this->findBy(['slug' => $slug]);
        if(!$question) return null;
        return $question[0];
    }

    public function getOnePage($pageNumber, $itemsOnPage)
    {
        return $this->createQueryBuilder('question')
            ->select('question')
            ->orderBy('question.createdAt', 'DESC')
            ->setMaxResults($itemsOnPage)
            ->setFirstResult(($pageNumber-1)*$itemsOnPage)
            ->getQuery()->getResult();
    }

    public function getTopXByAnswerCount($maxResults)
    {
        return $this->createQueryBuilder('question')
            ->select('question, count(answers.id) as hidden c')
            ->innerJoin('question.answers', 'answers')
            ->orderBy('c', 'DESC')
            ->groupBy('question.id')
            ->setMaxResults($maxResults)
            ->getQuery()->getResult();
    }

    public function getCountQuestionsInCategoryBySlug($slug)
    {
        /** @var Category $category */
        $category = $this->getEntityManager()->getRepository('AppBundle:Category')->findBy(['slug' => $slug]);

        if(!$category) return false;

        return $this->createQueryBuilder('question')
            ->select('count(question)')
            ->where(
                $this->getEntityManager()->getExpressionBuilder()
                    ->eq('question.category', ':category')
            )
            ->setParameter('category', $category[0])
            ->getQuery()->getSingleScalarResult();
    }

    public function getOnePageQuestionsInCategoryBySlug($pageNumber, $itemsOnPage, $slug)
    {
        /** @var Category $category */
        $category = $this->getEntityManager()->getRepository('AppBundle:Category')->findBy(['slug' => $slug]);

        if(!$category) return null;


        return $this->createQueryBuilder('question')
            ->select('question')
            ->where(
                $this->getEntityManager()->getExpressionBuilder()
                    ->eq('question.category', ':category')
            )
            ->setParameter('category', $category[0])
            ->orderBy('question.createdAt', 'DESC')
            ->setMaxResults($itemsOnPage)
            ->setFirstResult(($pageNumber-1)*$itemsOnPage)
            ->getQuery()->getResult();
    }
}
