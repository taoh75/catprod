<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category')]
    public function index(CategoryRepository $repo): Response
    {
        $categories = $repo->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }
    #[Route('/category/create', name: 'category_create')]
    public function new(CategoryRepository $repo,$id=0): Response
    {
        return $this->render('category/form.html.twig', [
            'categoryId' => $id,
            'category' => ["id"=>0,"name"=>"","active"=>true]
        ]);
    }

    #[Route('/category/{id}', name: 'category_update',methods: ['GET'])]
    public function update(CategoryRepository $repo,$id=0): Response
    {
        $category = $repo->findOneBy(['id'=>$id]);
        return $this->render('category/form.html.twig', [
            'categoryId' => $id,
            'category' => $category
        ]);
    }

    #[Route('/category/store', name: 'category_store',methods: ['POST'])]
    public function saveUpdate(Request $request, CategoryRepository $repo, ManagerRegistry $doctrine): Response
    {
        //Si el id del registro es 0 es porque el registro es nuevo por lo tanto lo creamos
        if ($request->get('id')==0) {
            $category = new Category();
            $category->setName($request->get('name'));
            $category->setActive((($request->get('active') == 'on') ? true : false));
            $category->setCreatedAt(new \DateTime('now'));
            $category->setUpdatedAt(new \DateTime('now'));
            $repo->add($category, true);
        }else{
            //Si id>0 entonces modificamos el registro
            $category = $repo->findOneBy(['id'=>$request->get('id')]);
            $category->setName($request->get('name'));
            $category->setActive((($request->get('active') == 'on') ? true : false));
            $category->setUpdatedAt(new \DateTime('now'));
            $repo->update($category,true);
        }
        return $this->redirect('/category');
    }

    #[Route('/category/delete/{id}', name: 'category_delete',methods: ['GET'])]
    public function remove(CategoryRepository $repo,$id=0): Response
    {
        $category = $repo->findOneBy(['id'=>$id]);
        $repo->remove($category,true);
        return $this->redirect('/category');
    }
}