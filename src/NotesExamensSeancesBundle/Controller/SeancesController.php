<?php

namespace NotesExamensSeancesBundle\Controller;

use NotesExamensSeancesBundle\Entity\Classe;
use NotesExamensSeancesBundle\Entity\Matiere;
use NotesExamensSeancesBundle\Entity\Professeur;
use NotesExamensSeancesBundle\Entity\Salle;
use NotesExamensSeancesBundle\Entity\Seance;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use UtilisateursBundle\Entity\User;

class SeancesController extends Controller
{


    public function listAction($id){
        $em = $this->getDoctrine()->getManager();
        $classe = $em->getRepository(\GestionNiveauxBundle\Entity\classe::class)->find($id);

        if(!($classe)){

            return $this->render('@NotesExamensSeances/error.html.twig', array( ));
        }
        else {

            if(isset($_POST['modifier']) && isset($_POST['EidMatiere']) && !empty($_POST['EidMatiere']) && isset($_POST['EidProf']) && !empty($_POST['EidProf'])  && isset($_POST['EidSalle']) && !empty($_POST['EidSalle'])  && isset($_POST['Ejour']) && !empty($_POST['Ejour'])  && isset($_POST['Edebut']) && !empty($_POST['Edebut']) && isset($_POST['Efin']) && !empty($_POST['Efin']) && isset($_POST['idSeance']) && !empty($_POST['idSeance']) )

            {
                $seance =  $em->getRepository(Seance::class)->find($_POST['idSeance']);

                $nvmatiere = $em->getRepository(\GestionNiveauxBundle\Entity\matiere::class)->find($_POST['EidMatiere']);
                $nvsalle = $em->getRepository(\BlocBundle\Entity\Salle::class)->find($_POST['EidSalle']);
                $nvprofesseur = $em->getRepository(User::class)->find($_POST['EidProf']);
                $seance->setSalle($nvsalle);
                $seance->setMatiere($nvmatiere);
                $seance->setClasse($classe);
                $seance->setProfesseur($nvprofesseur);
                $seance->setDebut(new \DateTime($_POST['Edebut']));
                $seance->setFin(new \DateTime($_POST['Efin']));
                $seance->setJour(new \DateTime($_POST['Ejour']));
                $em = $this->getDoctrine()->getManager();
                $em->flush();

            }



            if(isset($_POST['ajouter']) && isset($_POST['idMatiere']) && !empty($_POST['idMatiere']) && isset($_POST['idProf']) && !empty($_POST['idProf'])  && isset($_POST['idSalle']) && !empty($_POST['idSalle'])  && isset($_POST['jour']) && !empty($_POST['jour'])  && isset($_POST['debut']) && !empty($_POST['debut']) && isset($_POST['fin']) && !empty($_POST['fin']) )

            {
                $seance = new Seance();

                $nvmatiere = $em->getRepository(\GestionNiveauxBundle\Entity\matiere::class)->find($_POST['idMatiere']);
                $nvsalle = $em->getRepository(\BlocBundle\Entity\Salle::class)->find($_POST['idSalle']);
                $nvprofesseur = $em->getRepository(User::class)->find($_POST['idProf']);
                $seance->setSalle($nvsalle);
                $seance->setMatiere($nvmatiere);
                $seance->setClasse($classe);
                $seance->setProfesseur($nvprofesseur);
                $seance->setDebut(new \DateTime($_POST['debut']));
                $seance->setFin(new \DateTime($_POST['fin']));
                $seance->setJour(new \DateTime($_POST['jour']));
                $em = $this->getDoctrine()->getManager();
                $em->persist($seance);
                $em->flush();

            }


            $seances = $em->getRepository(Seance::class)->findBy(array('classe' => $id));
            $matieres = $em->getRepository(\GestionNiveauxBundle\Entity\matiere::class)->findBy(array('niveau' => $classe->getNiveau()->getId()));
            $salles = $em->getRepository(\BlocBundle\Entity\Salle::class)->findAll();

            $user = $this->get('security.token_storage')->getToken()->getUser();
            $id=$user->getId();

            $role="ROLE_PROFESSEUR";
            $em= $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();


            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->where('u.User = :user')
                ->andWhere('u.roles LIKE :roles')
                ->orderBy('u.searchName', 'ASC')
                ->setParameter('user', $id)
                ->setParameter('roles', '%"' . $role . '"%')
            ;

            $professeurs = $qb->getQuery()->getResult();









            return $this->render('@NotesExamensSeances/seancesecole.html.twig', array('seances' => $seances , 'classe' => $classe , 'matieres' => $matieres , 'salles' => $salles , 'professeurs' => $professeurs));



        }

    }


    public function deleteAction($id,$seanceid) {

        $em = $this->getDoctrine()->getManager();
        $examen = $em->getRepository(Seance::class)->find($seanceid);
        $em->remove($examen);
        $em->flush();

    }
}
