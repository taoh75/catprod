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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Mime\Email;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product', methods: ['GET','POST'])]
    public function index(CategoryRepository $catRepo, ProductRepository $repo, Request $request): Response
    {
        $categories=$catRepo->findBy(['active'=>true,'active'=>1]);
        $offset=max(0,$request->query->getInt('offset',0));
        $paginator = $repo->getProductPaginator($offset,$request);
        return $this->render('product/index.html.twig', [
            'products' => $paginator,
            'categories' => $categories,
            'offset' => $offset,
            'previous' => $offset - $repo::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $repo::PAGINATOR_PER_PAGE),
            'pagina' => ceil($offset/$repo::PAGINATOR_PER_PAGE)+1,
            'req' => $request
        ]);
    }
    //MODO SIN FORMULARIO
    #[Route('/products/create', name: 'create_product',methods: ['GET'])]
    public function create(CategoryRepository $categoryRepo): Response
    {
        $categories = $categoryRepo->findBy(['active'=>true,'active'=>1]);
        return $this->render('product/form.html.twig',['product'=>null,'categories'=>$categories]);
    }
    //MODO SIN FORMULARIO
    #[Route('/product/{id}', name: 'edit_product',methods: ['GET'])]
    public function edit(CategoryRepository $categoryRepo, ProductRepository $repo, $id=0): Response
    {
        $categories = $categoryRepo->findBy(['active'=>true,'active'=>1]);
        $product    = $repo->findOneBy(['id' => $id]);
        return $this->render('product/form.html.twig',['product'=>$product,'categories'=>$categories]);
    }
    //GUARDAR MODO SIN FORMULARIO
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
    //BORRAR NO REQUIERE FORMULARIO
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
    //CONSTRUIMOS EL ARCHIVO EN EXCEL
    #[Route('/products/export/xlsx', name: 'excel_product',methods: ['GET','POST'])]
    public function xlsx(Request $request, ProductRepository $repo, MailerInterface $mailer): Response
    {
        $prods=$repo->getProductExport($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Código');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Descripción');
        $sheet->setCellValue('D1', 'Marca');
        $sheet->setCellValue('E1', 'Categoria');
        $sheet->setCellValue('F1', 'Precio');
        $sheet->setCellValue('G1', 'Estado');

        $linea=2;
        foreach ($prods as $prod){
            $cat=$prod->getCategory();
            $sheet->setCellValue('A'.$linea, $prod->getCode());
            $sheet->setCellValue('B'.$linea, $prod->getName());
            $sheet->setCellValue('C'.$linea, $prod->getDescription());
            $sheet->setCellValue('D'.$linea, $prod->getBrand());
            $sheet->setCellValue('E'.$linea, $cat->getName());
            $sheet->setCellValue('F'.$linea, $prod->getPrice());
            $sheet->setCellValue('G'.$linea, (($prod->getActive()==1)?'Activo':'Inactivo'));
            $linea++;
        }

        $writer = new Xlsx($spreadsheet);
        if ($request->get('email')==''){
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'. urlencode('Products.xlsx').'"');
            $writer->save('php://output');
        }else{
            $writer->save('files/Product.xlsx');
            $email = (new Email())
                ->from('taoh75.pruebas@gmail.com')
                ->to($request->get('email'))
                ->subject('Lista de Productos')
                ->text('Se adjunta lista de Productos')
                ->html('<h3>CATPROD 1.0</h3><p>Adjuntamos lista de Productos a solicitud del usuario</p>')
                ->attachFromPath('/files/Product.xlsx');
            try {
                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                return new Response(json_encode($e,JSON_PRETTY_PRINT),200);
            }
        }
        return new Response("<script>history.back();</script>",200);
    }
}