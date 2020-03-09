<?php

namespace App\Controller;

use App\Entity\Content;
use App\Repository\ContentRepository;
use App\Service\EtagService;
use App\Service\SeoService;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContentController extends AbstractController
{

    // ...

    /**
     * @param Request $request
     * @param ContentRepository $contentRepository
     * @param SeoService $seoService
     * @param EtagService $etagService
     * @param int $page
     * @return Response
     * @throws NonUniqueResultException
     */
    public function list(Request $request, ContentRepository $contentRepository, SeoService $seoService,
                         EtagService $etagService, int $page = 1): Response
    {
        $lastModified = $contentRepository->getListingLastUpdatedAt();
        $lastModified = new \DateTime($lastModified ?: 'now');

        $response = new Response();
        $response->setSharedMaxAge(30);
        $response->setEtag(md5($lastModified->format('U') . $etagService->get()));
        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        }

        $contents = $contentRepository->findForListing($page, $request->query->get('q', null));

        $seoService->setRobots(SeoService::INDEX, SeoService::FOLLOW);

        return $this->render('content/list.html.twig', [
            'contents' => $contents,
        ], $response);
    }

    // ...

}