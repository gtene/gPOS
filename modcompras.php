<?php
include("config/configuration.php");
include("tool.php");
SimpleAutentificacionAutomatica("visual-iframe");

global $tamPagina;
$tamPagina  = 100;

function ListarProductos($idprov,$idmarca,$idcolor,$idtalla,
			 $seleccion,$idprod,$idbase,$nombre=false,$ref=false,
			 $cb=false,$obsoletos=false,$idlab=false,$idalias=false,
			 $porproveedor=false,$stockminimo=false){	
    global $action,$tamPagina;
    $oProducto    = new producto;
    $idalias      = ($nombre)? getLikeProductoAlias2Id($nombre, $IdIdioma=false):$idalias;
    $base         = $idbase;
    //$idprod???	
    $indice       = getSesionDato("PaginadorCompras");
    $Moneda       = getSesionDato("Moneda");

    $txtMoDet     = getModeloDetalle2txt();
    $txtModelo    = $txtMoDet[1];
    $txtDetalle   = $txtMoDet[2];
    
    $hayProductos = $oProducto->ListadoFlexibleCompras($idprov,$idmarca,$idcolor,$idtalla,false,
						       $indice,$base,false,false,$tamPagina,$ref,$cb,
						       $nombre,$obsoletos=false,$idalias,$idlab,
						       $porproveedor,$stockminimo);
    $num   = 0;
    $jsOut = "";
    $jsLex = new jsLextable;
    $jsOut .= jsLabel("color", $txtModelo);	
    $jsOut .= jsLabel("talla", $txtDetalle);
    $jsOut .= jsLabel("comprar",_("Comprar"));
    $jsOut .= jsLabel("modificar",_("Modificar"));
    $jsOut .= jsLabel("referencia",_("Referencia"));
    $jsOut .= jsLabel("unid",_("Unid"));
    $jsOut .= jsLabel("pv",_("PV"));
    $jsOut .= jsLabel("nuevatallacolor",_("Nuevo $txtModelo o $txtDetalle"));


    $oldId = -1;
    $num =0;		
    while ($oProducto->SiguienteProducto()){

        $num++;	
        $id = $oProducto->getId();
        $cb = $oProducto->getCB();
        $nombre = $oProducto->getNombre();
        $descripcion = $oProducto->get("Descripcion");
        $marca = getIdMarca2Texto($oProducto->get("IdMarca"));
	$lab = getNombreLaboratorio($oProducto->get("IdLabHab"));		
        $ref = $oProducto->getReferencia();
        $talla = getIdTalla2Texto( $oProducto->get("IdTalla"));
        $color = getIdColor2Texto( $oProducto->get("IdColor"));

        $manejaserie = $oProducto->get("Serie");
        $manejalote  = $oProducto->get("Lote");
        $manejafv    = $oProducto->get("FechaVencimiento");
	$eservicio   = ( $oProducto->get("Servicio") > 0 )? 1:0;//Servicio
	$eservicio   = ( $oProducto->get("MetaProducto") )? 1:$eservicio;//MetaProducto

        $lextalla = $jsLex->add($talla);
        $lexcolor = $jsLex->add($color);

        $fam = getIdFamilia2Texto( $oProducto->get("IdFamilia"));
        $sub = getIdSubFamilia2Texto($oProducto->get("IdFamilia"), $oProducto->get("IdSubFamilia"));


        $lexfam = $jsLex->add($fam);
        $lexsub = $jsLex->add($sub);

        $idBase = $oProducto->get("IdProdBase");
        if ($idBase != $oldId) {
            $ref = addslashes($ref);
            $nombre = addslashes($nombre);
            $jsOut .= "cLH($id,'$nombre','$ref',$lexfam,$lexsub,'$descripcion','$marca','$lab','$idBase');\n";
        }
        $jsOut .= "cL($id,$cb,$lextalla,$lexcolor,$manejaserie,$manejalote,$manejafv,$eservicio);\n";
        $oldId = $idBase;							
    }	

    $jsOut = $jsLex->jsDump() . $jsOut;

    $paginador = jsPaginador($indice,$tamPagina,$num);
    $jsOut .= $paginador;	
    $jsOut .= "cListProductos();";	
    $jsOut .= $paginador;
    $jsOut .= AutoOpen();

    $detadoc      = getSesionDato("detadoc");
    $documento    = getNombreDocumentoCompra($detadoc);
    $tipodoc      = $detadoc[0];
    $idprov       = (!$detadoc[1])?1:$detadoc[1];
    $nombreprov   = (!$idprov)?'CASAS VARIAS':$detadoc[2];    
    $fechapago    = $detadoc[8];
    $idsubsid     = (!$detadoc[9])?'':$detadoc[9];
    $nombresubsid = (!$detadoc[10])?'':$detadoc[10];
    $nrodoc       = $detadoc[3];
    $anrodoc      = explode("-", $nrodoc);
    $sdoc         = $anrodoc[0];
    $ndoc         = (isset($anrodoc[1]))? $anrodoc[1]:'';
    $fdoc         = $detadoc[4];
    $tipocambio   = $detadoc[6];
    $fcambio      = $detadoc[7];
    $tipomoneda   = $detadoc[5];
    $tpfecha      = 'Fecha Emisión : ';
    $checkigv     = (getSesionDato("incImpuestoDet")=='true')?'CHECKED':'';
    $checkipc     = (getSesionDato("incPercepcion")=='true')?'CHECKED':'';
    $checkcredt   = (getSesionDato("aCredito")=='true')?'CHECKED':'';
    $admiteCompra = (!selAdmite('Compras') );
    $tipodoc      = ( selAdmite('Compras') )? "O":$tipodoc;
    $checkF       = '';
    $checkO       = '';
    $checkR       = '';
    $checkG       = '';
    $checkSD      = '';

    switch($tipodoc) {
    case "F":
      $habilita=
	"apareceCapa('prov');".
	"apareceCapa('ndoc');".
	"apareceCapa('fdoc');".
	"apareceCapa('acred');".
	"apareceCapa('pgdoc');".
	"cambiodoc('F');";
      $checkF = 'selected';
      
      break;
    case "O":
      $habilita=
	"apareceCapa('prov');".
	"apareceCapa('acred');".
	"desapareceCapa('ndoc');".
	"apareceCapa('fdoc');".
	"apareceCapa('pgdoc');".
	"cambiodoc('O');".
	"CambiaTextDoc(1);";

      $checkigv = 'selected';
      setSesionDato("incImpuestoDet",'true');
      $checkO   = 'selected';
      break;
    case "R":
      $habilita=
	"apareceCapa('prov');".
	"apareceCapa('ndoc');".
	"apareceCapa('acred');".
	"apareceCapa('pgdoc');".
	"apareceCapa('fdoc');".
	"cambiodoc('R'); ";
      $checkR = 'selected';
      break;
    case "G":
      $habilita=
	"apareceCapa('prov');".
	"apareceCapa('ndoc');".
	"apareceCapa('fdoc');".
	"apareceCapa('acred');".
	"apareceCapa('pgdoc');".
	"cambiodoc('G'); ";
      $checkG = 'selected';
      break;
    case "SD":
      $habilita = 
	"apareceCapa('prov');".
	"desapareceCapa('ndoc');".
	"desapareceCapa('acred');".
	"apareceCapa('fdoc');".
	"apareceCapa('pgdoc');".
	"cambiodoc('SD'); ";
      $checkSD = 'selected';
    }
    $tnrodoc          = ( $nrodoc )?'Nro '.$nrodoc:'';
    $titulo           = ( selAdmite('Compras') )? 'Pedido':$documento.' '.$tnrodoc; 
    $tpfecha          = ( $detadoc[0]=='O' )?'Fecha Entrega : ':$tpfecha;
    $checkTS          = ( $tipomoneda==1 )? 'CHECKED':''; 
    $habilita_tmoneda = ( $tipomoneda==1 )? "desapareceCapa('cambiomoneda');":"";
    $checkTD          = ( $tipomoneda==2 )? 'CHECKED':''; 
    $habilita_tmoneda = ( $tipomoneda==2 )? "apareceCapa('cambiomoneda');":'';

    echo 
      "<script type='text/JavaScript' language='javascript' ".
      "src='modulos/calendario/calendar.js'></script>".
      "<script type='text/JavaScript' language='javascript' ".
      "src='modulos/calendario/lang/calendar-sp.js'></script>".
      "<script type='text/JavaScript' language='javascript' ".
      "src='modulos/calendario/calendar-setup.js'></script>

<center>
<table border='0' class='listado'>
  <tr class='formaCabeza'>
    <td colspan='3' height='16'>
      <div id='t_comprov' class='formaTitulo'>$titulo</div>
    </td>
  </tr>
  <tr>
  <td  class='lh' colspan='3' style='padding: 0em 1em 0.3em 0.6em'>

    <div id=prov style='display: none;color:#000000;'> 
      <b>Proveedor :</b>
      <input type=hidden id=IdProvHab name=IdProvHab value='$idprov' > 
      <input type=hidden id=modopagina name=modopagina value='Compras'>
      <input class=btn onclick='auxAltaProv();' type='button' value='+'> 
      <input class=btn onclick='auxProveedorHab();' type='button' value='...'> 
      <input class=xbtlh name=TextoProvHab id=TextoProvHab value='$nombreprov' readonly/>
    </div>

    &nbsp;&nbsp;&nbsp;&nbsp; 
      <b>Presupuesto :</b>
	<select>
                ";
    if($admiteCompra){
      echo "<option  onclick=".'"'."s_radioComprobante('F');".'"'."  $checkF >Factura</option>
		<option  onclick=".'"'."s_radioComprobante('R');".'"'."  $checkR>Boleta</option>
		<option  onclick=".'"'."s_radioComprobante('G');".'"'."  $checkG>Albarán</option>
		<option  onclick=".'"'."s_radioComprobante('SD');".'"'."  $checkSD>Ticket</option>";
    }
        echo "
	</select>
   </td>
  </tr>
  <tr>   
   <td colspan='3' style='' Align='center'>

     <div id=ndoc style='display: none;color:#000000;'>
       <b>Serie :</b> 
     <input class=cbt id='SDoc' name='SDoc' value='$sdoc' class='cajaPequena' type='text'
      size='4' maxlength='4' onkeypress='return soloAlfaNumericoSerieBase(event);' onblur='setndoc(this.value);' onkeyup='this.value=this.value.toUpperCase()'>
       <b>Nro :</b> 
     <input class=cbt id='NDoc' name='NDoc' value='$ndoc' class='cajaPequena' type='text' 
      size='7' maxlength='7' onkeypress='return soloNumerosEnterosBase(event,this.value);' onblur='setndoc(this.value);'>
     </div>



     <div id=fdoc style='display: none;color:#000000;'>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <b><span id='fecha_op'>$tpfecha</span></b> 

      <input class=cbt name='FechaDoc' type='text' id='FechaDoc' class='cajaPequena'
       size='8' maxlength='10' value='$fdoc' readonly> 

      <img  style='margin-top:-6px' src='img/gpos_calendario.png' name='Image1' id='Image11' 
       border='0'  onMouseOver=".'"'."this.style.cursor='pointer'".'"'." >
      <script>Calendar.setup( 
      {inputField : 'FechaDoc',ifFormat   : '%d/%m/%Y', button : 'Image11', onUpdate : setfechadoc });
      </script>

    </div>

    <div id=pgdoc style='display: none;color:#000000;'>
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <b> Fecha Pago :</b>
        <input class=cbt NAME='FechaPago' type='text' id='FechaPago' class='cajaPequena' 
         size='8' maxlength='10' value='$fechapago' readonly> 
         <img src='img/gpos_calendario.png' name='Image2' id='Image22' 
         border='0'  onMouseOver=".'"'."this.style.cursor='pointer'".'"'." >
        <script>Calendar.setup( 
        {inputField : 'FechaPago',ifFormat : '%d/%m/%Y', button : 'Image22', 
         onUpdate : setfechapagodoc });
        </script>
      </div>

   </td>
  </tr> 
  <tr>
    <td  class='lh'  colspan='3' style='padding: 0em 1em 0.2em 1em' Align='center'>
     
      <input type=radio id ='tipoSoles' name='grupo2' value='0' 
      onclick=".'"'."desapareceCapa('cambiomoneda'); cambiomoneda(1);".'"'." $checkTS >
      ".$Moneda[1]['T']."
      <input type=radio id='tipoDolar' name='grupo2' value='1' 
      onclick=".'"'."apareceCapa('cambiomoneda'); cambiomoneda(2);".'"'." $checkTD >
      ".$Moneda[2]['T']."

      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

        <div id=acred style='display: none;color:#000000; '>
        <input type=checkbox  onclick=aCredito(this.checked); $checkcredt >
        Crédito
        <input type=checkbox  onclick=incluirPercepcion(this.checked); $checkipc >
        Percepción
        </div> 
        <input type=checkbox  onclick=incluirIGV(this.checked); $checkigv >
        Impuesto


    </td>
  </tr>
  <tr>
   <td colspan='3' Align='center'>
     <div id=cambiomoneda style='display: none; color:#000000'> 

      <b>Cambio : </b>  
       <input class=cbt name='TipoCambio' class='InputPrecio'  value='$tipocambio' 
        onblur='settipocambio(this.value);' onkeypress='return soloNumerosBase(event,this.value);' size='5'>  
      <input class=cbt NAME='FechaCambio' type='text' id='FechaCambio' class='cajaPequena' 
       value='$fcambio'  size='8' maxlength='10' value='' readonly> 

      <img src='img/gpos_calendario.png' name='Image' id='Image'
       border='0'  onMouseOver=".'"'."this.style.cursor='pointer'".'"'.">
      <script>Calendar.setup( 
      {inputField : 'FechaCambio',ifFormat   : '%d/%m/%Y', button     : 'Image',  
       onUpdate : setfechacambio  });
      </script>

     </div>
   </td>
  </tr>
</table>
</center>

<script>$habilita $habilita_tmoneda parent.Compras_cambiaBtnCarrito('Ver');</script>";	
echo "<center>";
echo jsBody($jsOut);
echo "</center>";					

}

function old_ListarProductos($idprov,$idmarca,$idcolor,$idtalla,$seleccion,$idprod,$idbase) {
	//OBSOLETO
}



function PaginaBasica(){
	$actual    = getSesionDato("CarritoCompras");
	$idprov    = getSesionDato("FiltraProv");
	$idlab     = getSesionDato("FiltraLab");
	$idalias   = getSesionDato("FiltraAlias");
	$idmarca   = getSesionDato("FiltraMarca");
	$idcolor   = getSesionDato("FiltraColor");
	$idtalla   = getSesionDato("FiltraTalla");
	$idprod    = getSesionDato("FiltraIdProducto");
	$idbase    = getSesionDato("FiltraBase");	
	$nombre    = getSesionDato("FiltraNombre");
	$obsoletos = getSesionDato("FiltraObsoletos");
	$porproveedor = getSesionDato("FiltraPorProveedor");
	$stockminimo  = getSesionDato("FiltraStockMinimo");
	$ref       = getSesionDato("FiltraReferencia");
	$cb        = getSesionDato("FiltraCB");
	
	//echo q($idcolor,"color a mostrar");

	ListarProductos($idprov,$idmarca,$idcolor,$idtalla,
			$actual,$idprod,$idbase,$nombre,$ref,
			$cb,$obsoletos,$idlab,$idalias,
			$porproveedor,$stockminimo);
	//OperacionesConProductos();	
}


function QuitarDeCarritoCompras($id){
	$actual = getSesionDato("CarritoCompras");
	$cantidad = getSesionDato("CarroCostesCompra");
	
	$actual[$id] = false;
	$cantidad[$id] = false;
				
	setSesionDato("CarritoCompras",$actual);	
	setSesionDato("CarroCostesCompra",$cantidad);
}

function ListarOpcionesSeleccion(){
	echo gas("titulo",_("Operaciones sobre la selección"));
	echo "<table border=1>";
	echo "<tr><td>"._("Hacer una compra a proveedores")."</td><td>".gModo("comprar",_("Comprar"))."</td></tr>";
	echo "<tr><td>"._("Buscar en el almacén")."</td><td>".gModo("transsel",_("Buscar"))."</td></tr>";
	//echo "<tr><td>"._("Cambio global de precio")."</td><td>".gModo("preciochange",_("Precios"))."</td></tr>";
	echo "</table>";	
}



function ListaFormaDeUnidades() {

    //Se usa esto aqui?
    //FormaListaCompraCantidades	
  global $action,$tamPagina;
    $oProducto = new producto; 

    $ot = getTemplate("FormaListaCompraCantidades");
    if (!$ot){	
        error(__FILE__ . __LINE__ ,"Info: template no encontrado");
        return false; }

        $ot->resetSeries(array("IdProducto","Referencia","Nombre",
            "tBorrar","tEditar","tSeleccion","vUnidades"));

        //$tamPagina      = $ot->getPagina();
        $detadoc        = getSesionDato("detadoc");
	$documento      = getNombreDocumentoCompra($detadoc);
        $indice         = getSesionDato("PaginadorSeleccionCompras2");			
        $carrito        = getSesionDato("CarritoCompras");
        $costescarrito  = getSesionDato("CarroCostesCompra");
        $DestinoAlmacen = getSesionDato("DestinoAlmacen");	
	$descuentos     = getSesionDato("descuentos");
	$Moneda         = getSesionDato("Moneda");
        $quitar         = _("Quitar");
	$DestinoAlmacen = (!$DestinoAlmacen)? getParametro("AlmacenCentral"):$DestinoAlmacen;
        $salta          = 0;
        $num            = 0;

        $ot->fijar("tTitulo",_("Detalle de ".$documento.": ".count($carrito)." productos"));
        $ot->fijar("comboAlmacenes",genComboAlmacenes($DestinoAlmacen));

        if ($carrito)
            foreach ( $carrito as $key=>$value){		

	      if ($num <= $tamPagina ){		

		$num++;			
		$cb     = ($oProducto->Load($key))? $oProducto->getCB():"";
		$nombre = ($oProducto->Load($key))? $oProducto->getDescripcion()." ".
		                                    getIdMarca2Texto($oProducto->get("IdMarca"))." ".
		                                    $oProducto->getColorTexto()." ".
		                                    $oProducto->getTallaTexto()." ".
		                                    getNombreLaboratorio($oProducto->get("IdLabHab")):"";	
		$vdescuento = (isset($descuentos[$key][0]))? $descuentos[$key][0]/$value:0;
		$vcoste     = round( $costescarrito[$key]-$vdescuento , 2);

		$item = $num;
		//if($indice==10||$indice==20) $item--;

		$ot->fijarSerie("vItem",$item.".");		
		$ot->fijarSerie("vReferencia",$cb);		
		$ot->fijarSerie("vNombre",$nombre);
		$ot->fijarSerie("tBorrar",$quitar);
		$ot->fijarSerie("vUnidades",$value." ".$oProducto->getUnidadMedida());
		$ot->fijarSerie("vPrecio",$vcoste);
		$ot->fijarSerie("IdProducto",$oProducto->getId());
	      }
            }

        $tpfecha      = 'Fecha Emisión : ';
        $tipodoc      = $detadoc[0];
    	$aCredito     = (getSesionDato("aCredito")!='true')?'hidden':'';
        $nrodoc       = $detadoc[3];
	$anrodoc      = explode("-", $nrodoc);
	$sdoc         = $anrodoc[0];
	$ndoc         = (isset($anrodoc[1]))? $anrodoc[1]:'';
	$tnrodoc      = ($nrodoc)?'Nro '.$nrodoc:'';
	$titulo       = $documento.' '.$tnrodoc;
	$tpfecha      = ($detadoc[0]=='O')?'Fecha Entrega : ':$tpfecha;
        $idprov       = $detadoc[1];
        $nomprov      = $detadoc[2];
        $nrodoc       = $detadoc[3];
        $fecdoc       = $detadoc[4];
        $tipomoneda   = $detadoc[5];
        $checked      = ($tipomoneda==1)? $Moneda[1]['T']:$Moneda[2]['T'];
        $tipocambio   = $detadoc[6];
        $fechacambio  = $detadoc[7];
        $fechapago    = $detadoc[8];
	$idsubsid     = $detadoc[9];
	$nombresubsid = $detadoc[10];
	$tcp          = (getSesionDato("incImpuestoDet")=='true')? 'Precio/Unid.':'Costo/Unid.';

        $ot->fijar("vTDoc",$tipodoc);
        $ot->fijar("vTipoDoc",$titulo);
        $ot->fijar("vIdProvHab",$idprov);
        $ot->fijar("vProveedorHab",$nomprov);
        $ot->fijar("vNDoc",$nrodoc);
	$ot->fijar("vCP",$tcp);
        $ot->fijar("vFechaDoc",$fecdoc);
        $ot->fijar("vFechaTxDoc",$tpfecha);
        $ot->fijar("vTipoMoneda",$tipomoneda);
        $ot->fijar("vTipoMoneda2",$checked);
        $ot->fijar("vTipoCambio",$tipocambio);
        $ot->fijar("vFechaCambio",$fechacambio);
        $ot->fijar("vFechaPago",$fechapago);
        $ot->fijar("vFletadorHab",$nombresubsid);
        $ot->fijar("aCredito",$aCredito);

        $ot->paginador($indice,false,$num);	
        $ot->fijar("action",$action );
        $ot->terminaSerie();

        echo $ot->Output();	
}

function AutoOpen(){

        if( !isset($_SESSION["IdUltimoCambioProductos"]) ) return;

        $mod = $_SESSION["IdUltimoCambioProductos"];

	if (!$mod) return"//no hay ultimod";

	$id = $mod;	
	//$_SESSION["IdUltimoCambioProductos"] = false;
	return "\n MuestraBases($id);\n";	
}

function ActualizarCantidad($Id, $UnidNew, $PrecioNew){
	if (!$Id or $Id=="")
		return;
	
	$data = getSesionDato("CarritoCompras");
	$data2 = getSesionDato("CarroCostesCompra");
			
	$data[$Id] = $UnidNew;
	$data2[$Id] = $PrecioNew;
		
	setSesionDato("CarritoCompras",$data);		
	setSesionDato("CarroCostesCompra",$data2);
}


function ActualizarAlmacen(){
	$IdLocal = CleanID($_POST["IdLocal"]);
	if ($IdLocal)
		setSesionDato("DestinoAlmacen",$IdLocal);							
}

function ReseleccionarLocal() {
	global $action;
	
	$ot = getTemplate("ElijeLocalCompra");
	if (!$ot){	
		error(__FILE__ . __LINE__ ,"Info: template no encontrado");
		return false; }

	$ot->fijar("tTitulo",_("Elije local destino"));
	$ot->fijar("comboAlmacenes",getSesionDato("ComboAlmacenes"));
	
	$ot->fijar("action",$action);	
	echo $ot->Output();
}




function VaciarPedidosBasedatos() {
	if (!isUsuarioAdministradorWeb())
		return;
	//	query("DELETE FROM ges_pedidos");
	//	query("DELETE FROM ges_pedidosdet");
}


function CreardeCeroCarro() { 
		$unidades = array();
		$precios = array();
		$carro = getSesionDato("CarritoCompras");
		foreach ($carro as $key=>$value){			
			$unidades[$key]=0;
			$precios[$key]=0;
		}	
		setSesionDato("CarritoCompras",$unidades);
		setSesionDato("CarroCostesCompra",$precios);
}

function getSiguienteId($tabla,$columna){
    $num = 1;
    $sql = "SELECT MAX($columna) as Total FROM $tabla";
    $row = queryrow($sql);
    if($row){
        $num = $row["Total"]+1;
    }
    return $num;
}
function obtenerIdCompra($idOrden,$idproducto){
    $sql = "SELECT IdPedidoDet FROM ges_pedidosdet WHERE IdPedido='$idOrden' AND IdProducto='$idproducto'";
    $row=queryrow($sql);
    if($row){
        return $row["IdPedidoDet"];
    }
    return null;
}
function obtenerUnidadMedida($idproducto){
    $sql = "SELECT UnidadMedida FROM ges_productos WHERE IdProducto='$idproducto'";
    $row=queryrow($sql);
    if($row){
        return $row["UnidadMedida"];
    }
    return null;
}

function implota($fecha) // bd2local
{
	if (($fecha == "") || ($fecha == "0000-00-00"))
		return "";
	$vector_fecha = explode("-",$fecha);
	$aux = $vector_fecha[2];
	$vector_fecha[2] = $vector_fecha[0];
	$vector_fecha[0] = $aux;
	return implode("/",$vector_fecha);
}

function explota($fecha) // local2bd
{
	$vector_fecha = explode("/",$fecha);

	if(count($vector_fecha) < 3) return $fecha;

	$aux = $vector_fecha[2];
	$vector_fecha[2] = $vector_fecha[0];
	$vector_fecha[0] = $aux;
	return implode("-",$vector_fecha);
}

PageStart();

echo gas("cabecera",_("Presupuestos"));


//Paginadores
switch($modo){		

		case "buscarproductos":
			//QuitarFiltrosAvanzados();
			setSesionDato("FiltraCB",false);	
			setSesionDato("FiltraIdProducto",false);			
			setSesionDato("FiltraReferencia",false);
			setSesionDato("FiltraNombre",false);
			setSesionDato("FiltraObsoletos",false);
			setSesionDato("FiltraStockMinimo",false);
			setSesionDato("FiltraPorProveedor",false);
			setSesionDato("FiltraProv",false);

			setSesionDato("PaginadorCompras",0);
	
			$referencia  = ( isset($_GET["Referencia"]))? CleanReferencia($_GET["Referencia"]):'';		
			$cb 	     = ( isset($_GET["CodigoBarras"]))? CleanCB($_GET["CodigoBarras"]):'';		
			$nombre       = ( isset($_GET["Nombre"]))? CleanText($_GET["Nombre"]):'';
			$obsoletos    = ( isset($_GET["Obsoletos"]))? CleanID($_GET["Obsoletos"]):'';
			$porproveedor = ( isset($_GET["PorProveedor"]))? CleanID($_GET["PorProveedor"]):'';
			$stockminimo  = ( isset($_GET["StockMinimo"]))? CleanID($_GET["StockMinimo"]):'';
			$detadoc      = getSesionDato("detadoc");
			$idprov       = (!$detadoc[1])?1:$detadoc[1];

			if (strlen($referencia)<1) $referencia = false;		
			if (strlen($cb)<1) $cb = false;	
			if ($cb) setSesionDato("FiltraCB",$cb);	
			setSesionDato("FiltraReferencia",$referencia);			
			setSesionDato("FiltraNombre",$nombre);
			setSesionDato("FiltraObsoletos",$obsoletos);
			setSesionDato("FiltraPorProveedor",$porproveedor);
			setSesionDato("FiltraProv",$idprov);
			setSesionDato("FiltraStockMinimo",$stockminimo);
			
			PaginaBasica();
			break;
		
		case "buscaporcb":
			$cb = CleanCB($_POST["CodigoBarras"]);
			if (!$cb)
				$cb = CleanCB($_GET["CodigoBarras"]);
			
			$completas = ($_POST["verCompletas"]=="on");
			
			$id = getIdFromCodigoBarras($cb);				
			
			
			if ($id) {
				if ($completas) {
					$base = getProdBaseFromId($id);		
					setSesionDato("FiltraBase",$base);	
				} else {										
					setSesionDato("FiltraIdProducto",$id);
				}
			} else {
				setSesionDato("FiltraBase",false);	
				setSesionDato("FiltraIdProducto",false);				
			}
			setSesionDato("FiltraNombre",false);
			
			PaginaBasica();
			
			break;
			
		case "mostrar":
		
		$reset = false;
		$id =  CleanID($_GET["IdProveedor"]);
		if ($id != getSesionDato("FiltraProv") ) {
			setSesionDato("FiltraProv",$id);
			$reset = true;
		}
			
		$id =  CleanID($_GET["IdTalla"]);			
		if ($id != getSesionDato("FiltraTalla")) {
			setSesionDato("FiltraTalla",$id);
			$reset = true;
		}

		$id =  intval($_GET["IdColor"]);
		//echo q($id,"color leido");								
		if ($id != getSesionDato("FiltraColor")) {
			setSesionDato("FiltraColor",$id);
			$reset = true;			
			//echo q($id,"nuevo color");
		}
		
		$id =  CleanID($_GET["IdMarca"]);
		if ($id != getSesionDato("FiltraMarca")) {
			setSesionDato("FiltraMarca",$id);
			$reset = true;			
		}		
				
		setSesionDato("FiltraBase",false);
		setSesionDato("FiltraIdProducto",false);
		setSesionDato("FiltraNombre",false);
						
		if ($reset) {
			setSesionDato("PaginadorCompras",0);//Puede haber ahora muchos menos
		}						
				
		PaginaBasica();
		break;

				
	case "spagmenos":
		$indice = getSesionDato("PaginadorSeleccionCompras");
		$indice = $indice - $tamPagina;
		if ($indice<0)
			$indice = 0;
		setSesionDato("PaginadorSeleccionCompras",$indice);
		PaginaBasica();
		break;	

	case "spagmas":
		$indice = getSesionDato("PaginadorSeleccionCompras");
		$indice = $indice + $tamPagina;
		setSesionDato("PaginadorSeleccionCompras",$indice);
		PaginaBasica();
		break;			
	case "pagmenos":
		$indice = getSesionDato("PaginadorCompras");
		$indice = $indice - $tamPagina;
		if ($indice<0)
			$indice = 0;
		setSesionDato("PaginadorCompras",$indice);
		PaginaBasica();
		break;	
	case "pagmas":
		$indice = getSesionDato("PaginadorCompras");
		$indice = $indice + $tamPagina;
		setSesionDato("PaginadorCompras",$indice);
		PaginaBasica();
		break;				
    
	case "agnadircb":
		$cb = CleanCB($_GET["CodigoBarras"]);		
		$id = getIdFromCodigoBarras($cb);
		
		if ($id) {
			AgnadirCarritoCompras($id);			
			if (isVerbose())	  					
				echo gas("nota",_("Producto seleccionado ($id)"));
		} else {		
			if (isVerbose())
				echo gas("nota",_("Producto no encontrado"));
		}		
		PaginaBasica();		
		break;		
		
	case "agnadirporreferencia":
		$ref = CleanReferencia($_GET["referencia"]);		
		$id = BuscaProductoPorReferencia($ref);
		
		if ($id) {
			AgnadirCarritoCompras($id);			
			if (isVerbose())	  					
				echo gas("nota",_("Producto seleccionado ($id)"));
		} else {		
			if (isVerbose())
				echo gas("nota",_("Producto no encontrado"));
		}		
		PaginaBasica();		
		break;		
	case "vaciarpedidos":
		VaciarPedidosBasedatos();
		break;	
	case "ajustarcantidades":		
		ActualizarAlmacen();
		Actualizarantidades();
		if(isVerbose())
			echo gas("aviso","cantidades actualizadas");
		ListaFormaDeUnidades();
		break;
	
	case "comprarPaso3":
	        ActualizarAlmacen();//Actualiza sesion dato -DestinoAlmacen- IdLocal Seleccionado
		ActualizarCantidades();//Actualiza sesion dato -CarritoCompras- y -CarroCostesCompra-
		//echo gas("aviso","comprando...");
		$IdLocal  = getSesionDato("DestinoAlmacen");
		$IdTienda = getSesionDato("IdTienda");
		$detadoc  = getSesionDato("detadoc");
		//Control Carrito
		if(!validarOrdenDeCompra($IdLocal))
		  { echo gas("aviso","Carrito Vacio.");break; }
		//Control Local destino
		if ( !$IdLocal and $IdLocal=="nada" )
		  { ReseleccionarLocal(); break; }

		$IdOrden = CrearOrdenDeCompra($IdLocal);
		if($detadoc[0]!="O"){
		  //Solo Comprobantes de Compras 
		  registraImportes($IdOrden);
		  registrarVencimiento($IdOrden);
		  registrarLote($IdOrden);
		}
		
		ResetearCarritoCompras();//Vaciamos carrito, pues fue ejecutado
		//Separador();			

		//MENSAJE
		$nomdes  = getNombreLocalId($IdLocal);
		$nomdoc  = getNombreDocumentoCompra($detadoc);
		$linkdoc = ($detadoc[0]=="O")?'verOrdenCompraConfirmado':'verPedidoConfirmado';
		$nrodoc  = ($detadoc[11])?$detadoc[3]:$IdLocal.$IdOrden;
		$coddoc  = ($detadoc[0]=="O")?$nrodoc:$detadoc[3];
		$coddoc  = ($detadoc[0]=="SD")? $IdLocal.'-'.$IdOrden:$coddoc;
		$xdocum  = $nomdoc." Nro. ".$coddoc;
		$xlocal  = "Local ".$nomdes;
		$xrecibir= ( $nomdoc == "Pedido" )? "style='display:none'":"";

		echo _("<center>
                          <div class='forma' style='width: 200px'>
                            
                            <ul class='auxmenu'>
                             <li class='lh' style='font-weight: bold;padding:.5em;font-size:13px'>
                                 Se ha realizado su alta</li>
                             <li class='lh' style='font-size:14px;'>".$xdocum."</li>
                             <li class='lh' style='font-size:13px;'>".$xlocal."</li>
                             <li class='auxitem'>
                                <input class='btn item' type='button' value='Ver ".$nomdoc."' 
                                       onclick='".$linkdoc."(".$IdOrden.")'>
                             </li>
                             <li class='auxitem' ".$xrecibir.">
                                 <input class='btn item' type='button' value='Recibir ".$nomdoc."' 
                                        onclick='verRecibirCompra()'>
                             </li>
                             <li class='auxitem'>
                             <hr width='100%'>
                             </li>
                             <li class='auxitem'>
                             <input class='btn item' type='button' value='Volver al Listado' 
                                    onclick='nuevaCompraBuscar()'>
                             </li>
                            </ul>
                            </div>
                           </center>");


		break;	
		
	case "borrarpaso2": //Desseleccionar articulo
		ActualizarAlmacen();
		ActualizarCantidades();
				
		$id = CleanID($_GET["id"]);		
		QuitarDeCarritoCompras($id);
		if (isVerbose())
			echo gas("nota",_("Producto sacado de carrito"));						
	case "continuarCompra":
               //.... antes se creaba el carro aqui

		$id = CleanID(GET("IdLocal"));
		if ($id)
		  setSesionDato("DestinoAlmacen",$id);

	case "editarCompra":
		ListaFormaDeUnidades();
		break;		
	case "filtrarproveedor":
		$id= CleanID($_GET["IdProveedor"]);
				
		if ($id) {
			setSesionDato("CompraProveedor",$id);
			setSesionDato("PaginadorCompras",0);//Puede haber ahora muchos menos
		}		
		
		//Reseteamos carrito (no queremos mezclar productos de diferentes proveedores
		//setSesionDato("CarritoCompras",false);
		
		
				
		PaginaBasica();
		break;		

	case "desselec": //Desseleccionar articulo
		
		$id = CleanID($_GET["id"]);
		
		QuitarDeCarritoCompras($id);
		
		if (isVerbose()) echo gas("nota",_("Producto sacado de carrito"));		

		PaginaBasica();							
		
		break;
	case "selec"://Seleccion articulo
		$id = CleanID($_GET["id"]);
		AgnadirCarritoCompras($id);
				  		
		if (isVerbose()) echo gas("nota",_("Producto seleccionado"));		

		PaginaBasica();							
			
		break;
	case "borrar":
		$id = CleanID($_GET["id"]);
		
		if (!productoEnAlmacen($id)){		
			BorrarProducto($id);
		} else {
			echo gas("nota",_("No se puede borrar porque aun hay existencias. Primero vacié en almacenes.") );
		} 				
		//Separador();
		PaginaBasica();	
		break;	

	default:
		if(strlen($modo)>0) {
			if (isVerbose())
				echo "<br>No se capturo el evento '$modo'<br>";
		}
		PaginaBasica();
				
		break;		
}

PageEnd();

?>
