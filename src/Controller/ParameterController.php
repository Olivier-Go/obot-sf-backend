<?php

namespace App\Controller;

use App\Entity\Parameter;
use App\Form\ParameterType;
use App\Repository\ParameterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parameter")
 */
class ParameterController extends AbstractController
{
    /**
     * @Route("/", name="parameter_index")
     */
    public function index(Request $request, ParameterRepository $parameterRepository): Response
    {
        $parameter = $parameterRepository->findFirst() ?? new Parameter();
        $form = $this->createForm(ParameterType::class, $parameter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $parameterRepository->add($parameter);
            $this->addFlash('success', 'Paramètres modifiés');
            return $this->redirectToRoute('parameter_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('parameter/index.html.twig', [
            'form' => $form,
        ]);
    }
}
