<?php

namespace AppBundle\Controller;

use Knp\Bundle\SnappyBundle\Snappy;

use AppBundle\Entity\GlobalValue;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use DateTime;
use DateInterval;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfile;

use \FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Printercomponent controller.
 *
 * @Route("Printercomponent")
 */
class PrintercomponentController extends FOSRestController
{

    /**
     * @Route("/pdfprint4/{id}", name="Printercomponent_pdfprint4")
     * @Method({"GET","POST"})
     */
    public function pdfprint4Action(Request $request)
    {
        try {
           
            $connector = new WindowsPrintConnector("smb://62597-NOTE/POS58");  
            
            $Printercomponent = new Printercomponent($connector);
            $Printercomponent->setJustification(Printercomponent::JUSTIFY_CENTER);
            $Printercomponent->setEmphasis(true);
            $Printercomponent->text("Roma Helados \n");
            $Printercomponent->text("\n");
            
            $Printercomponent->setEmphasis(false);
            $Printercomponent->setJustification(Printercomponent::JUSTIFY_LEFT);
            
            $Printercomponent->text("Printercomponent Nro: $id \n");
           
            $hora = $Printercomponent->getHoraEntregaFormatHMS();
            $Printercomponent->text("Hora de Entrega: $hora \n");
            
            $cliente = $Printercomponent->getContacto();
            $Printercomponent->text("Cliente: $cliente \n");
            
            $texto = $Printercomponent->getDireccionFormat();
            $Printercomponent->text("Direccion: $texto \n");
            
            $texto = $Printercomponent->getTelefono();
            $Printercomponent->text("Telefono: $texto \n");
            
            $Printercomponent->setEmphasis(true);
            
            $Printercomponent->text("\n");
            $Printercomponent->text("Helados Elegidos \n");
            $titulo = str_pad("N°", 2) . str_pad("Pote", 7) . str_pad("Sabor", 15) . str_pad("Cantidad", 8); 
            $Printercomponent->text($titulo);
            
            $Printercomponent->setEmphasis(false);
            //DEtalle del Printercomponent

            foreach ($Printercomponent->getPrintercomponentdetalles() as $item) { 

                $Printercomponent->text("\n");
                $pote = str_pad($item->getNropote(), 2);
                $Printercomponent->text($pote);

                $pote = str_pad($item->getMedidaPoteFormat(), 7);
                $Printercomponent->text($pote);

                $producto = str_pad($item->getProducto()->getNombre(),15);
                $Printercomponent->text($producto);
                
                $cantidad = str_pad($item->getCantidadString(),8);
                $Printercomponent->text($cantidad);
    
                
            }
            
            $Printercomponent->text("\n");
            $Printercomponent->text("\n");
            $Printercomponent->setJustification(Printercomponent::JUSTIFY_RIGHT);
            $Printercomponent->setEmphasis(true);
            $texto = $Printercomponent->getMontoFormat();
            $Printercomponent->text("Total: $texto \n");
  
            $texto =  $Printercomponent->getMontoAbonaFormat();
            $Printercomponent->text("Abona Con: $texto \n");
            $Printercomponent->text("\n");
            $Printercomponent->text("\n");
            $Printercomponent->text("\n");
            $Printercomponent->text("\n");
            $Printercomponent->text("\n");
            
            $Printercomponent->cut();
            $Printercomponent->close();
            
            /* Actualizar flag Impreso en Printercomponent*/ 
            $em = $this->getDoctrine()->getManager();
            $Printercomponent->setImpreso(true);
            $em->persist($Printercomponent);
            $em->flush();
            $this->addFlash( 'success','Imprimiendo...');
            /* Actualizar flag Impreso en Printercomponent*/ 
            return $this->redirectToRoute('Printercomponentdetalle_new',array('Printercomponent_id'=> $Printercomponent->getId()));

        } catch(Exception $e) {
            echo "Couldn't print to this Printercomponent: " . $e -> getMessage() . "\n";
        }
               

    }

    




}

    

    

    


