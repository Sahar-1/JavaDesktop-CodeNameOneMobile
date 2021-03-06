<?php

namespace UtilisateursBundle\Controller;


use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use http\Client\Curl\User;
use MailBundle\Entity\mail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use UtilisateursBundle\Entity\niveau;
use UtilisateursBundle\Entity\classe;
use GestionNiveauxBundle\Entity\matiere;

use UtilisateursBundle\Form\classeType;
use UtilisateursBundle\Form\etudiantType;
use UtilisateursBundle\Form\niveauType;
use UtilisateursBundle\Form\professeurType;
use UtilisateursBundle\Form\UserType;



use UtilisateursBundle\Repository\UserRepository;

class DefaultController extends Controller
{
    /*public function indexAction()
    {
        return $this->render('@Utilisateurs/Default/index.html.twig');
    }*/








//professeur


    public function createProfesseurAction(Request $request)
    {
      $user=new \UtilisateursBundle\Entity\User();

      $user->setUser($this->get('security.token_storage')->getToken()->getUser());
      $user->setRoles(array("ROLE_PROFESSEUR"));
        $user->setEnabled(true);

        $form = $this->createForm(professeurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $professor = $form->getData();
            $professor->setSearchName($professor->getUsername());


            $to = $form["email"]->getData();
            $username=$form["username"]->getData();
            $password=$form["plainPassword"]->getData();


            $transport = \Swift_SmtpTransport::newInstance()
                ->setUsername('edplusplus@outlook.com')->setPassword('Ed101010')
                ->setHost('smtp-mail.outlook.com')
                ->setPort(587)->setEncryption('tls');

            $mailer = \Swift_Mailer::newInstance($transport);

            $message = \Swift_Message::newInstance()
                ->setSubject('ED PLUS | Compte Professeur cr??e')
                ->setFrom(array('edplusplus@outlook.com' => 'ED +'))
                ->setTo($to)
                ->setBody("Your username is ".$username."**** your password is ".$password);
            ;

            $result = $mailer->send($message);


            $em = $this->getDoctrine()->getManager();

            $em->persist($professor);
            $em->flush();

            return $this->redirectToRoute('professor_homepage');
        }

        return $this->render('@Utilisateurs/FormViews/createProfesseur.html.twig', [
            'form'=>$form->createView()]);

    }

    public function deleteProfesseurAction($id)
    {
        $em = $this->getDoctrine()->getManager(); //entity manager

        $professeur = $this->getDoctrine()->getRepository('UtilisateursBundle:User')->find($id);
        $em->remove($professeur);
        $em->flush();//confirm the query to the database
        return $this->redirectToRoute('professor_homepage');
    }

    public function updateProfesseurAction($id,Request $request)
    {
        $professeur = $this->getDoctrine()->getRepository('UtilisateursBundle:User')->find($id);
        $professeur->setSearchName($professeur->getUsername());

        $form = $this->createForm(professeurType::class,$professeur);



        $form->remove('ajouter');
        $form->add('Modifier',SubmitType::class,['attr' => ['class' => 'btn btn-gradient-primary mr-2'],]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $professeur = $form->getData();
            $professeur->setSearchName($professeur->getUsername());



            $em = $this->getDoctrine()->getManager();
            $em->persist($professeur);
            $em->flush();

            return $this->redirectToRoute('professor_homepage');
        }

        return $this->render('@Utilisateurs/FormViews/createProfesseur.html.twig', [
            'form'=>$form->createView()]);

    }

   /* public function searchProfesseurAction(Request $request)
    {   $username=$request->get('username') ;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $role="ROLE_PROFESSEUR";
        $qb = $em->createQueryBuilder();
        if($username!=null) {


            $qb->select('u')
            ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
            ->where('u.searchName = :str')
            ->andWhere('u.roles LIKE :roles')
            ->andWhere('u.User = :user')


            ->setParameter('user', $id)
            ->setParameter('str', $username)
            ->setParameter('roles', '%"' . $role . '"%');}
        else{
            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u')
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')
                ->setParameter('user', $id)
                ->setParameter('roles', '%"' . $role . '"%');

        }


        $users = $qb->getQuery()->getResult();

        $matiere = $this->getDoctrine()->getRepository('GestionNiveauxBundle:matiere')->findAll();

        return $this->render('@Utilisateurs/professor.html.twig', ['professors'=>$users,'matiere'=>$matiere]);



    }*/

    public function searchProfesseurByMatiereAction(Request $request)
    {//search by matiere
        $matiere=$request->get('matiere',0)[0] ;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $role="ROLE_PROFESSEUR";


        $qb = $em->createQueryBuilder();
        if($matiere!=null) {
            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->where('u.matiere = :str')

                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')
                ->orderBy('u.searchName', 'ASC')
                ->setParameter('user', $id)
                ->setParameter('str', $matiere)
                ->setParameter('roles', '%"' . $role . '"%');
        }

        else{
            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')
                ->setParameter('user', $id)
                ->setParameter('roles', '%"' . $role . '"%');

        }
        $users = $qb->getQuery()->getResult();




        $matiere = $this->getDoctrine()->getRepository('GestionNiveauxBundle:matiere')->findAll();

        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator=$this->get('knp_paginator');
        $result= $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page',1),
            $request->query->getInt('limit',5)
        );


        return $this->render('@Utilisateurs/professor.html.twig', ['professors'=>$result,'matiere'=>$matiere]);



    }

    public function searchProfesseurAjaxAction(Request $request)
    {  $requestString=$request->get('q') ;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $role="ROLE_PROFESSEUR";
        $qb = $em->createQueryBuilder();
        if($requestString!=null) {


            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->where('u.searchName = :str')
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')


                ->setParameter('user', $id)
                ->setParameter('str', $requestString)
                ->setParameter('roles', '%"' . $role . '"%');
        }


        $users = $qb->getQuery()->getResult();

        if(!$users)
        {
            $result['users']['error']="professor not found";
        }
        else{
            $result['users']=$this->getProfesseurEntities($users);
        }

        return new Response(json_encode($result));


    }

    public function getProfesseurEntities($users)
    {
        foreach ($users as $users)
        {
            $realEntities[$users->getId()] =[$users->getUsername(),$users->getEmail(),$users->getMatiere()->getNom()];
        }
        return $realEntities;
    }

    public function professorAction(Request $request)
    {


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

        $user = $qb->getQuery()->getResult();
        $query=$em->createQuery($qb);
        $matiere = $this->getDoctrine()->getRepository('GestionNiveauxBundle:matiere')->findAll();

        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator=$this->get('knp_paginator');
       $result= $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page',1),
           $request->query->getInt('limit',5)



        );


        //$etudiants = $this->getDoctrine()->getRepository('UtilisateursBundle:User')->findBy(array('User'=>$id,'roles'=>$role));



        return $this->render('@Utilisateurs/professor.html.twig', ['professors'=>$result,'matiere'=>$matiere]);

    }




//etudiant

    public function createEtudiantAction(Request $request)
    {
        $user=new \UtilisateursBundle\Entity\User();

        $user->setUser($this->get('security.token_storage')->getToken()->getUser());
        $user->setRoles(array("ROLE_ETUDIANT"));
        $user->setEnabled(true);
        $form = $this->createForm(etudiantType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $etudiant = $form->getData();
            $etudiant->setSearchName($etudiant->getUsername());
            $nbEtudiant= $etudiant->getClasse()->getNbrEtudiant();
            $etudiant->getClasse()->setNbrEtudiant($nbEtudiant+1);

            $to = $form["email"]->getData();
            $username=$form["username"]->getData();
            $password=$form["plainPassword"]->getData();


            $transport = \Swift_SmtpTransport::newInstance()
                ->setUsername('edplusplus@outlook.com')->setPassword('Ed101010')
                ->setHost('smtp-mail.outlook.com')
                ->setPort(587)->setEncryption('tls');

            $mailer = \Swift_Mailer::newInstance($transport);

            $message = \Swift_Message::newInstance()
                ->setSubject('ED PLUS | Compte etudiant cr??e')
                ->setFrom(array('edplusplus@outlook.com' => 'ED +'))
                ->setTo($to)
                ->setBody("Votre compte etudiant a ??t?? cr??er avec succ??e  - USER : ".$username." Mote de Passe : ".$password);
            ;

            $result = $mailer->send($message);

            $em = $this->getDoctrine()->getManager();

            $em->persist($etudiant);
            $em->flush();


            return $this->redirectToRoute('etudiant_homepage');
        }

        return $this->render('@Utilisateurs/FormViews/createEtudiant.html.twig', [
            'form'=>$form->createView()]);

    }

    public function deleteEtudiantAction($id)
    {
        $em = $this->getDoctrine()->getManager(); //entity manager

        $etudiant = $this->getDoctrine()->getRepository('UtilisateursBundle:User')->find($id);
        $nbEtudiant= $etudiant->getClasse()->getNbrEtudiant();
        $etudiant->getClasse()->setNbrEtudiant($nbEtudiant-1);
        $em->persist($etudiant);

        $em->remove($etudiant);
        $em->flush();//confirm the query to the database
        return $this->redirectToRoute('etudiant_homepage');
    }

    public function updateEtudiantAction($id,Request $request)
    {
        $etudiant = $this->getDoctrine()->getRepository('UtilisateursBundle:User')->find($id);
        $form = $this->createForm(etudiantType::class, $etudiant);



        $form->remove('ajouter');
        $form->add('Modifier',SubmitType::class,['attr' => ['class' => 'btn btn-gradient-primary mr-2'],]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $etudiant = $form->getData();
            $etudiant->setSearchName($etudiant->getUsername());
            $nbEtudiant= $etudiant->getClasse()->getNbrEtudiant();
            $etudiant->getClasse()->setNbrEtudiant($nbEtudiant+1);


            $em = $this->getDoctrine()->getManager();
            $em->persist($etudiant);
            $em->flush();

            return $this->redirectToRoute('etudiant_homepage');
        }

        return $this->render('@Utilisateurs/FormViews/createEtudiant.html.twig', [
            'form'=>$form->createView()]);

    }

    /*public function searchEtudiantAction(Request $request)
    {  $username=$request->get('username') ;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $role="ROLE_ETUDIANT";
        $qb = $em->createQueryBuilder();
        if($username!=null) {


            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->where('u.searchName = :str')
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')


                ->setParameter('user', $id)
                ->setParameter('str', $username)
                ->setParameter('roles', '%"' . $role . '"%');}
        else{
            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u')
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')
                ->setParameter('user', $id)
                ->setParameter('roles', '%"' . $role . '"%');

        }


        $users = $qb->getQuery()->getResult();

        $classe = $this->getDoctrine()->getRepository('GestionNiveauxBundle:classe')->findAll();

        return $this->render('@Utilisateurs/etudiant.html.twig', ['etudiants'=>$users,'classe'=>$classe]);


    }*/

    public function searchEtudiantByClasseAction(Request $request)
    {//search by matiere
        $classe=$request->get('classe',0)[0] ;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $role="ROLE_ETUDIANT";


        $qb = $em->createQueryBuilder();
        if($classe!=null) {
            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->where('u.classe = :str')
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')
                ->orderBy('u.classe', 'ASC')

                ->setParameter('user', $id)
                ->setParameter('str', $classe)
                ->setParameter('roles', '%"' . $role . '"%');
        }

        else{
            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')
                ->setParameter('user', $id)
                ->setParameter('roles', '%"' . $role . '"%');

        }
        $users = $qb->getQuery()->getResult();




        $classe = $this->getDoctrine()->getRepository('GestionNiveauxBundle:classe')->findAll();

        $paginator=$this->get('knp_paginator');
        $result= $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page',1),
            $request->query->getInt('limit',5)
        );

        return $this->render('@Utilisateurs/etudiant.html.twig', ['etudiants'=>$result,'classe'=>$classe]);



    }



    public function searchEtudiantAjaxAction(Request $request)
    {  $requestString=$request->get('q') ;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $em = $this->getDoctrine()->getManager();
        $role="ROLE_ETUDIANT";
        $qb = $em->createQueryBuilder();
        if($requestString!=null) {


            $qb->select('u')
                ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
                ->where('u.searchName = :str')
                ->andWhere('u.roles LIKE :roles')
                ->andWhere('u.User = :user')


                ->setParameter('user', $id)
                ->setParameter('str', $requestString)
                ->setParameter('roles', '%"' . $role . '"%');
        }


        $users = $qb->getQuery()->getResult();

        if(!$users)
        {
            $result['users']['error']="student not found";
        }
        else{
            $result['users']=$this->getEtudiantEntities($users);
        }

       return new Response(json_encode($result));


    }

    public function getEtudiantEntities($users)
    {
        foreach ($users as $users)
        {
            $realEntities[$users->getId()] =[$users->getUsername(),$users->getEmail(),$users->getClasse()->getNom()];
        }
        return $realEntities;
    }




    public function etudiantAction(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $id=$user->getId();

        $role="ROLE_ETUDIANT";
        $em= $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();


        $qb->select('u')
            ->from('UtilisateursBundle:User', 'u') // Change this to the name of your bundle and the name of your mapped user Entity
            ->where('u.User = :user')
            ->andWhere('u.roles LIKE :roles')
            ->orderBy('u.classe', 'ASC')
            ->setParameter('user', $id)
            ->setParameter('roles', '%"' . $role . '"%');

        $user = $qb->getQuery()->getResult();






        //$etudiants = $this->getDoctrine()->getRepository('UtilisateursBundle:User')->findBy(array('User'=>$id,'roles'=>$role));
        $classe = $this->getDoctrine()->getRepository('GestionNiveauxBundle:classe')->findAll();

        /**
         * @var $paginator \Knp\Component\Pager\Paginator
         */
        $paginator=$this->get('knp_paginator');
        $result= $paginator->paginate(
            $qb->getQuery(),
            $request->query->getInt('page',1),
            $request->query->getInt('limit',5)
        );

        return $this->render('@Utilisateurs/etudiant.html.twig', ['etudiants'=>$result,'classe'=>$classe]);

    }












    public function showEtudiantAction()
    {
        return $this->render('@Utilisateurs/etudiant_home.html.twig');
    }

    public function showEcoleAction()
    {
        return $this->render('@Utilisateurs/ecole_home.html.twig');
    }

    public function showProfesseurAction()
    {
        return $this->render('@Utilisateurs/professeur_home.html.twig');
    }


    public function redirectAction()
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ETUDIANT')) {
            return $this->redirectToRoute('etudiant_home');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_PROFESSEUR')) {
            return $this->redirectToRoute('professeur_home');
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('ecole_home');
        }

        return $this->redirectToRoute('fos_user_security_login');

    }










    public function chartAction()
    {
        $pieChart = new PieChart();
        $em= $this->getDoctrine();
        $classes = $em->getRepository('GestionNiveauxBundle:classe')->findAll();
        $totalEtudiant=0;
        foreach($classes as $classe) {
            $totalEtudiant=$totalEtudiant+$classe->getNbrEtudiant();
        }

        $data= array();
        $stat=['classe', 'NbrEtudiant'];
        $nb=0;
        array_push($data,$stat);

        foreach($classes as $classe) {
            $stat=array();
            if($totalEtudiant==0)
            {
                $pourcentage = 0;
            }
            else {
                $pourcentage = ($classe->getNbrEtudiant() *100) /$totalEtudiant ;
            }
            array_push($stat,$classe->getNom(),($pourcentage));

            $stat=[$classe->getNom() ,$pourcentage];
            array_push($data,$stat);

        }

        $pieChart->getData()->setArrayToDataTable(
            $data
        );
        $pieChart->getOptions()->setTitle('Pourcentages des ??tudiants par classe');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);


        return $this->render('@Utilisateurs/classeChart.html.twig', array('piechart' => $pieChart));
    }



    public function matiereAction()
    {


        $matiere = $this->getDoctrine()->getRepository('UtilisateursBundle:matiere')->findAll();
        return $this->render('@Utilisateurs/professor.html.twig', ['matiere'=>$matiere]);

    }






}
