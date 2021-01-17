<?php


namespace App\DataPersister;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Experience;
use App\Entity\Techno;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;


class ExperienceDataPersister implements ContextAwareDataPersisterInterface
{
    private $_entityManager;
    private $_slugger;
    private $_request;

    public function __construct(EntityManagerInterface $entityManager, SluggerInterface $slugger, RequestStack $request)
    {
        $this->_entityManager = $entityManager;
        $this->_slugger = $slugger;
        $this->_request = $request->getCurrentRequest();
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Experience;
    }

    public function persist($data, array $context = [])
    {

        $data->setSlug(
            $this
                ->_slugger
                ->slug($data->getTitle())
        );


        $technoRepository = $this->_entityManager->getRepository(Techno::class);
        foreach ($data->getTechnos() as $techno) {
            $t = $technoRepository->findOneByName($techno->getName());

            // if the tag exists, don't persist it
            if ($t !== null) {
                $data->removeTechno($techno);
                $data->addTechno($t);
            } else {
                $this->_entityManager->persist($techno);
            }
        }


        $this->_entityManager->persist($data);
        $this->_entityManager->flush();
    }

    public function remove($data, array $context = [])
    {
        $this->_entityManager->remove($data);
        $this->_entityManager->flush();
    }
}