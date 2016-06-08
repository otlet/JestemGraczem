<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use AppBundle\Entity\User;

class StreamController extends Controller
{
    /**
     * @Route("/stream", name="api.stream")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getRepository('AppBundle:User');

        $stream = $em->createQueryBuilder('p')
            ->select('p.username', 'p.twitch')
            ->where('p.twitch IS NOT NULL')
            ->orderBy('p.partner', 'DESC')
            ->getQuery()
            ->getResult();

        if ($stream == NULL) {
            return new Response(
                "{name:NULL,status:-1,message:'ERROR 404 - Streamów nie znaleziono!'}",
                Response::HTTP_NOT_FOUND,
                ['content-type' => 'application/json']
            );
        }

        $encoders = [
            new XmlEncoder(),
            new JsonEncoder()
        ];

        $normalizers = [
            new ObjectNormalizer()
        ];

        $serializer = new Serializer($normalizers, $encoders);

        return new Response(
            $serializer->serialize($stream, 'json'),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     * @Route("/stream/p/{id}", name="api.stream.id")
     */
    public function streamAction($id = 1)
    {
        $stream = $this->getDoctrine()->getRepository('AppBundle:Stream')->findOneBy(['id' => $id]);

        if ($stream == NULL) {
            return new Response(
                "{name:NULL,status:-1,message:'ERROR 404 - Stream nie znaleziono!'}",
                Response::HTTP_NOT_FOUND,
                ['content-type' => 'application/json']
            );
        }

        $encoders = [
            new XmlEncoder(),
            new JsonEncoder()
        ];

        $normalizers = [
            new ObjectNormalizer()
        ];

        $serializer = new Serializer($normalizers, $encoders);

        return new Response(
            $serializer->serialize($stream, 'json'),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }
}
