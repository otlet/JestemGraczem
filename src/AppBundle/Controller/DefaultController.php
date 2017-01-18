<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $seo = $this->container->get('sonata.seo.page');
        $seo->setTitle('Strona główna :: JestemGraczem.pl')
            ->addMeta('name', 'description', "JestemGraczem.pl jest to pierwszy w Polsce portal poświęcony graczom, a nie samym grom!")
            ->addMeta('property', 'og:title', 'Strona główna :: JestemGraczem.pl')
            ->addMeta('property', 'og:url', $this->get('router')->generate('homepage', [], UrlGeneratorInterface::ABSOLUTE_URL));

        $articles = $this->getDoctrine()->getRepository('NewsBundle:News')->createQueryBuilder('m')
            ->where('m.promoted = 1')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(3)
            ->getQuery()->getResult();

        $mem = $this->getDoctrine()->getRepository('AppBundle:Meme')->findOneBy(['promoted' => true], ['id' => 'DESC']);

        $video = $this->getDoctrine()->getRepository('AppBundle:Video')->createQueryBuilder('m')
            ->where('m.promoted = 1')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(8)
            ->getQuery()->getResult();

        $sliders = $this->getDoctrine()->getRepository('AppBundle:Slider')->createQueryBuilder('m')
            ->where('m.enabled = 1')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults(5)
            ->getQuery()->getResult();

        $avatar = ($this->getUser()) ? md5($this->getUser()->getEmail()) : md5('thejestemgraczemsquad@gmail.com');

        return $this->render($this->getParameter('theme') . '/default/index.html.twig', [
            'articles' => $articles,
            'meme' => $mem,
            'video' => $video,
            'avatar' => $avatar,
            'sliders' => $sliders
        ]);
    }

    /**
     * @Route("/test", name="test")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function testAction()
    {

        $avatar = ($this->getUser()) ? md5($this->getUser()->getEmail()) : md5('thejestemgraczemsquad@gmail.com');

        return $this->render($this->getParameter('theme') . '/default/test.html.twig', [
            'avatar' => $avatar
        ]);
    }

    /**
     * @Route("/redirect", name="redirect")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function redirectAction()
    {
        if (isset($_GET['url']) && !preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $_GET['url'])) {
            return $this->redirectToRoute('homepage');
        }

        if (isset($_GET['r']) && $_GET['r'] == TRUE) {
            return $this->redirect($_GET['url']);
        }

        return $this->render($this->getParameter('theme') . '/default/frame.html.twig', [
            'url' => $_GET['url'],
        ]);
    }

    /**
     * @Route("/u/{user}", name="user")
     * @param $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userSiteAction($user)
    {

        $em = $this->getDoctrine()->getRepository('AppBundle:User');

        $user = $em->createQueryBuilder('p')
            ->select(
                'p.id',
                'p.username',
                'p.twitch',
                'p.beampro',
                'p.youtube',
                'p.partner',
                'p.premium',
                'p.editor',
                'p.description',
                'p.email',
                'p.steam',
                'p.battlenet',
                'p.lol',
                'p.steam',
                'p.localization',
                'p.profilePicturePath'
            )
            ->where('p.username = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            $this->addFlash(
                'error',
                'Kurde, nie znaleźliśmy tego co poszukujesz :('
            );
            throw $this->createNotFoundException('Nie ma takiego użytkownika!');
        }

        $mem = $this->getDoctrine()->getRepository('AppBundle:Meme')->findBy(['user' => $user['id']]);
        $video = $this->getDoctrine()->getRepository('AppBundle:Video')->findBy(['user' => $user['id']]);

        $seo = $this->container->get('sonata.seo.page');
        $seo->setTitle('Profil: ' . $user['username'] . ' :: JestemGraczem.pl')
            ->addMeta('name', 'description', "Profil użytkownika " . $user['username'] . " na portalu JestemGraczem.pl")
            ->addMeta('property', 'og:title', $user['username'])
            ->addMeta('property', 'og:type', 'profile')
            ->addMeta('property', 'og:url', $this->get('router')->generate('user', ['user' => $user['username']], UrlGeneratorInterface::ABSOLUTE_URL));

        return $this->render($this->getParameter('theme') . '/default/user.html.twig', [
            'user' => $user,
            'meme' => $mem,
            'video' => $video
        ]);
    }

}
