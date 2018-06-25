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
            if (array_key_exists ('id', $json['pedido'])){
                $pedido_id = $json['pedido']['id'];
                $param_post_request_json      = json_encode(array('pedido'=> array('id'=>$pedido_id))); 
            }

            //Llamar a Api Rest con nro de pedido
            $url_api = "http://127.0.0.1:7002/api/pedido/findbyid";
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POSTFIELDS, $param_post_request_json );
            curl_setopt($curl, CURLOPT_URL, $url_api);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);

            $json = json_decode($result, true);
            

         
        



            //Imprimir ticket
            $connector = new WindowsPrintConnector("smb://62597-NOTE/POS58");  
            //$connector = new WindowsPrintConnector("smb://127.0.0.1:7001/POS58");  
            $printercomponent = new Printer($connector);
            $printercomponent->setJustification(Printer::JUSTIFY_CENTER);
            $printercomponent->setEmphasis(true);
            $printercomponent->text("Roma Helados \n");

            $printercomponent->setEmphasis(false);
            $printercomponent->setJustification(Printer::JUSTIFY_LEFT);
                       

            if (!empty($json)){
                
                if (array_key_exists ('id', $json['data'])){
                    $texto = "Pedido Nro: " . $json['data']['id'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }

                if (array_key_exists ('fecha', $json['data'])){
                    $texto = "Fecha: " . $json['data']['fecha'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }

                if (array_key_exists ('horaentrega', $json['data'])){
                    $texto = "Hora de Entrega: " . $json['data']['horaentrega'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }

                if (array_key_exists ('contacto', $json['data'])){
                    $texto = "Cliente: " . $json['data']['contacto'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }

                if (array_key_exists ('direccion', $json['data'])){
                    $texto = "Direccion: " . $json['data']['direccion'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }

                if (array_key_exists ('telefono', $json['data'])){
                    $texto = "Telefono: " . $json['data']['telefono'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }




               
                //Detalle
                $printercomponent->setEmphasis(true);
            
                $printercomponent->text("\n");
                $printercomponent->text("Helados Elegidos \n");
                $titulo = str_pad("N°", 2) . str_pad("Pote", 7) . str_pad("Sabor", 15) . str_pad("Cantidad", 8); 
                $printercomponent->text($titulo);
                $printercomponent->setEmphasis(false);

                if (array_key_exists ('pedidodetalles', $json['data'])){    
                    $detalles = $json['data']['pedidodetalles'];
                    foreach ($detalles as $item){
                      
                        $printercomponent->text("\n");
                        $nro_pote = str_pad($item['producto']['nropote'], 2);
                        $printercomponent->text($nro_pote);
        
                        $pote = str_pad($item['producto']['medidapote'], 7);
                        $printercomponent->text($pote);
        
                        $producto = str_pad($item['producto']['nombre'],15);
                        $printercomponent->text($producto);
                        
                        $cantidad = str_pad($item['producto']['cantidad'],8);
                        $printercomponent->text($cantidad);
            


                    }
                }
                
                //Fin detalle

                //Footer

                if (array_key_exists ('montoabona', $json['data'])){
                    $texto = "Abona Con: " . $json['data']['montoabona'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }
                if (array_key_exists ('monto', $json['data'])){
                    $texto = "Monto: " . $json['data']['monto'];
                    $printercomponent->text($texto);
                    $printercomponent->text("\n");
                }
                //Fin Footer 

                $printercomponent->cut();
                $printercomponent->close();
                $response->setContent(json_encode($json));


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


function CallAPI($method, $url, $data = false)
{
    $curl = curl_init();

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
  //  curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);

    return $result;
}

}
