<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Knp\Bundle\SnappyBundle\Snappy;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;
use Symfony\Component\HttpFoundation\JsonResponse;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }





     /**
     * @Route("/printer/ticket/", name="printerticket")
     */
    public function printerticketAction(Request $request)
    {
        try {
            $content = $request->getContent();


            //$response = new JsonResponse();
            $response = new Response();
            $response->headers->set('Content-Type', 'application/json');
            // Allow all websites
            $response->headers->set('Access-Control-Allow-Origin', '*');

            $code    = Response::HTTP_OK; 
            $message ='OK'; 
            $result  = "";
            $json    = json_decode($content, true);
            //print $json;
            //print $content; 
            
           
            $connector = new WindowsPrintConnector("smb://62597-NOTE/POS58");  
            //$connector = new WindowsPrintConnector("smb://127.0.0.1:7001/POS58");  
            $Printercomponent = new Printer($connector);
            $Printercomponent->setJustification(Printer::JUSTIFY_CENTER);
            $Printercomponent->setEmphasis(true);
            $Printercomponent->text("Roma Helados \n");
            $Printercomponent->text("\n");
            $Printercomponent->setEmphasis(true);
            
            if (!empty($json)){
                if (array_key_exists ('user_id', $json['pedido'])){
                    $Printercomponent->text($json['pedido']['user_id']);
                    $Printercomponent->text("\n");
                }

                if (array_key_exists ('pedidodetalles', $json['pedido'])){    
                    $detalles = $json['pedido']['pedidodetalles'];
                    foreach ($detalles as $item){
                        $Printercomponent->text($item['producto_id']);
                        $Printercomponent->text("\n");
                    }
                }

                $Printercomponent->cut();
                $Printercomponent->close();
                
                $data = array('code'=>'200',
                    'message'=>'ok',
                    'data'=>$json
                );
                $response->setContent(json_encode($data));


            }else{
                $data = array('code'=>'500',
                'message'=>'error',
                'data'=>'puto',
                'content'=>$content
                );
                $response->setContent(json_encode($data));
            }
            
          
            
            return $response;

        } catch(Exception $e) {
            $data = array('code'=>'200',
                'message'=>'ok',
                'data'=>$e->getMessage()
            );
            $response->setData($data);
            return $response;
        }

       
    }


}
