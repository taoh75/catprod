<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\NewProductType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product', methods: ['GET'])]
    public function index(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
    #[Route('/products/create', name: 'create_product',methods: ['GET'])]
    public function create(CategoryRepository $categoryRepo): Response
    {
        $categories = $categoryRepo->findBy(['active'=>true,'active'=>1]);
        return $this->render('product/form.html.twig',['product'=>null,'categories'=>$categories]);
    }

    #[Route('/product/{id}', name: 'edit_product',methods: ['GET'])]
    public function edit(CategoryRepository $categoryRepo, ProductRepository $repo, $id=0): Response
    {
        $categories = $categoryRepo->findBy(['active'=>true,'active'=>1]);
        $product    = $repo->findOneBy(['id' => $id]);
        return $this->render('product/form.html.twig',['product'=>$product,'categories'=>$categories]);
    }

    #[Route('/products/store', name: 'store_product',methods: ['POST'])]
    public function store(Request $req, ProductRepository $repo, CategoryRepository $catRepo): Response
    {
        $category=$catRepo->findOneBy(['id'=>$req->get('category')]);
        if ($req->get('id')==0){
            $product=new Product();
            $product->setCreatedAt(new \DateTime('now'));
        }else{
            $product=$repo->findOneBy(['id'=>$req->get('id')]);
        }
        $product->setCode($req->get('code'));
        $product->setName($req->get('name'));
        $product->setDescription($req->get('description'));
        $product->setBrand($req->get('brand'));
        $product->setCategory($category);
        $product->setPrice(floatval($req->get('price')));
        $product->setActive((($req->get('active')=='on')?true:false));
        $product->setUpdatedAt(new \DateTime('now'));
        $repo->add($product,true);
        return $this->redirect('/products');
    }

    #[Route('/product/delete/{id}', name: 'delete_product',methods: ['GET'])]
    public function delete(ProductRepository $repo, $id=0): Response
    {
        $product = $repo->findOneBy(['id' => $id]);
        $repo->remove($product,true);
        return $this->redirect('/products');
    }

    //ESTA RUTA MUESTRA LA PANTALLA DE CREACIÓN DE UN PRODUCTO CON EL BUILDER FORM
    #[Route('/products/new', name: 'new_product',methods: ['GET','POST'])]
    public function new(Request $request, ProductRepository $repo): Response
    {
        $product = new Product();
        $form = $this->createForm(NewProductType::class,$product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $product->setCreatedAt(new \DateTime('now'));
            $product->setUpdatedAt(new \DateTime('now'));
            $repo->add($product,true);
            return $this->redirectToRoute('app_product');
        }
        return $this->renderForm('product/new.html.twig',['form'=>$form]);
    }
    //ESTA RUTA MUESTRA LA PANTALLA DE MODIFICACION DE UN PRODUCTO CON EL BUILDER FORM
    #[Route('/products/update/{id}', name: 'update_product',methods: ['GET','POST'])]
    public function update(Request $request, ProductRepository $repo,$id=0): Response
    {
        $product = $repo->findOneBy(['id'=>$id]);
        $form = $this->createForm(NewProductType::class,$product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $product->setActive($form->get('active')->getData()=='on');
            $product->setUpdatedAt(new \DateTime('now'));
            $repo->add($product,true);
            return $this->redirectToRoute('app_product');
        }
        return $this->renderForm('product/new.html.twig',['form'=>$form]);
    }
}