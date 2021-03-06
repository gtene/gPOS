<?php

include ("tool.php");

SimpleAutentificacionAutomatica("visual-iframe");

$tamPagina = 10;

function ListarProveedores() {
	//Creamos template
	global $action, $tamPagina;

	$ot = getTemplate("ListadoProveedores");
	if (!$ot) {
		error(__FILE__.__LINE__, "Info: template no encontrado");
		return false;
	}
	echo gas("cabecera", _("Gestión de Laboratorios"));
	$marcado = getSesionDato("CarritoProv");

	//echo "ser: " . serialize($marcado). "<br>";

	$oProveedor = new laboratorio;

	$indice = getSesionDato("PaginadorProv");

	$hayProveedores = $oProveedor->Listado(false, $indice);
    $ot->fijar("tAviso", _("¿Esta seguro de que quiere eliminarlo?"));

	if (!$hayProveedores) {
		echo gas("aviso", "No hay laboratorios disponibles");
	} else {
		$ot->fijar("tTitulo", _("Lista de Laboratorio"));
		$ot->fijar("action", $action);
		$ot->fijar("tBorrar", _("Eliminar"));
		$ot->fijar("tEditar", _("Modificar"));
		$ot->fijar("tProveedor", _("Laboratorio"));
		$ot->resetSeries(array ("IdProveedor", "Referencia", "Nombre", "tBorrar", "tEditar", "tSeleccion", "marca"
			,"vNombreComercial"));
		$num = 0;
		while ($oProveedor->SiguienteLaboratorio()) {
			$num ++;
			$id = $oProveedor->getId();
			$ot->fijarSerie("IdProveedor", $id);
			$ot->fijarSerie("IdSubsidiario", $id);
			$ot->fijarSerie("tNombreComercial",_("Nombre comercial"));
			$ot->fijarSerie("vNombreComercial",$oProveedor->get("NombreComercial"));
			if ($marcado and in_array($id, $marcado)) {
				$ot->fijarSerie("marca", "<abbr title='Seleccion' style='color:red'>S</abbr>");
				$ot->fijarSerie("tSeleccion", "");
				$ot->eliminaSeccion("s$num");
			} else {
				$ot->fijarSerie("marca", "");
				$ot->fijarSerie("tSeleccion", _("Selección"));
			}
		}


		$ot->paginador($indice, false, $num);

		$ot->terminaSerie(false);
		echo $ot->Output();
	}
}

function MostrarProveedorParaEdicion($id, $lang) {
	global $action;

	$oProveedor = new laboratorio;
	if (!$oProveedor->Load($id, $lang)) {
		error(__FILE__.__LINE__, "W: no pudo mostrareditar '$id'");
		return false;
	}

	$ot = getTemplate("ModificarProveedor");
	if (!$ot) {
		error(__FILE__.__LINE__, "Info: template no encontrado");
		return false;
	}

	$txtCuentaEmp1    = ($oProveedor->get("CuentaBancaria") == 0)? "":getNroCuenta($oProveedor->get("CuentaBancaria"));
	$txtCuentaEmp2    = ($oProveedor->get("CuentaBancaria2") == 0)? "":getNroCuenta($oProveedor->get("CuentaBancaria2"));

	$ot->fijar("action", $action);
	$ot->fijar("vIdProveedor", $id);
	
	$ot->fijar("tModPagoHabitual", _("Modo pago hab."));
	$ot->fijar("vIdModPagoHabitual", $oProveedor->get("IdModPagoHabitual"));	
	$ot->fijar("comboModPagoHabitual", genComboModalidadPago( $oProveedor->get("IdModPagoHabitual")));
	
	$ot->campo(_("Pagina web"), "PaginaWeb", $oProveedor);
	$ot->fijar("comboIdPais" ,genComboPaises($oProveedor->get("IdPais")));
			
	$ot->fijar("tIdPais", _("País"));			
	$ot->fijar("tTitulo", _("Modificando laboratorio"));
	$ot->campo(_("Nombre comercial"), "NombreComercial", $oProveedor);
	$ot->campo(_("Nombre legal"), "NombreLegal", $oProveedor);
	$ot->campo(_("Dirección"), "Direccion", $oProveedor);
	$ot->campo(_("Localidad"), "Localidad", $oProveedor);
	$ot->campo(_("Código postal"), "CP", $oProveedor);
	$ot->campo(_("Telf.(1)"), "Telefono1", $oProveedor);
	$ot->campo(_("Telf.(2)"), "Telefono2", $oProveedor);
	$ot->campo(_("Contacto"), "Contacto", $oProveedor);
	$ot->campo(_("Cargo"), "Cargo", $oProveedor);
	$ot->campo(_("Email"), "Email", $oProveedor);
	$ot->fijar("CuentaBancaria", _("CTA Moneda Principal"));
	$ot->fijar("CuentaBancaria2", _("CTA Moneda Secundaria"));
	$ot->fijar("vCuentaBancaria", $txtCuentaEmp1);
	$ot->fijar("vCuentaBancaria2", $txtCuentaEmp2);
	$ot->fijar("vIdCuentaBancaria", $oProveedor->get("CuentaBancaria"));
	$ot->fijar("vIdCuentaBancaria2", $oProveedor->get("CuentaBancaria2"));
	$ot->campo(_("Número fiscal (RUC)"), "NumeroFiscal", $oProveedor);
	$ot->campo(_("Comentarios"), "Comentarios", $oProveedor);	
	

	echo $ot->Output();
}


function OperacionesConProveedores() {
	if (!isUsuarioAdministradorWeb())
		return;
		
	echo gas("titulo", _("Operaciones sobre Laboratorios"));
	echo "<table border=1>";
	echo "<tr><td>"._("Crear un nuevo laboratorio")."</td><td>".gModo("alta", _("Alta"))."</td></tr>";
	echo "<tr><td style='color:red'>Debug: vaciar laboratorios</td><td>".gModo("vaciarbasededatos", _("Eliminar todo"))."</td></tr>";
	echo "</table>";
}

function FormularioAlta($esPopup=false) {
	global $action;

	$oProveedor = new laboratorio;

	$oProveedor->Crea();

	if($esPopup)
	  echo "<script> function loadfocus(){ }</script>";

	$ot = getTemplate("FormAltaProveedor");
	if (!$ot) {
		error(__FILE__.__LINE__, "Info: template no encontrado");
		return false;
	}
	$ot->fijar("action", $action);
	$ot->fijar("tTitulo", _("Alta laboratorio"));	
	
	$ot->fijar("tModPagoHabitual", _("Modo pago hab."));
	$ot->fijar("vIdModPagoHabitual", $oProveedor->get("IdModPagoHabitual"));	
	$ot->fijar("comboModPagoHabitual", genComboModalidadPago( $oProveedor->get("IdModPagoHabitual")));
	
	$ot->campo(_("Pagina web"), "PaginaWeb", $oProveedor);
	
	$ot->fijar("tIdPais", _("País"));
	$ot->fijar("comboIdPais" ,genComboPaises($oProveedor->get("IdPais")));
		
	$ot->campo(_("Nombre comercial"), "NombreComercial", $oProveedor);
	$ot->campo(_("Nombre legal"), "NombreLegal", $oProveedor);
	$ot->campo(_("Dirección"), "Direccion", $oProveedor);
	$ot->campo(_("Localidad"), "Localidad", $oProveedor);
	$ot->campo(_("Código postal"), "CP", $oProveedor);
	$ot->campo(_("Telf.(1)"), "Telefono1", $oProveedor);
	$ot->campo(_("Telf.(2)"), "Telefono2", $oProveedor);
	$ot->campo(_("Contacto"), "Contacto", $oProveedor);
	$ot->campo(_("Cargo"), "Cargo", $oProveedor);
	$ot->campo(_("Email"), "Email", $oProveedor);
	$ot->campo(_("Cuenta bancaria"), "CuentaBancaria", $oProveedor);
	$ot->campo(_("Número fiscal (RUC)"), "NumeroFiscal", $oProveedor);
	$ot->campo(_("Comentarios"), "Comentarios", $oProveedor);
	
	if ($esPopup) {
		$ot->fijar("vesPopup", 1);
		$ot->fijar("onClose", "parent.closepopup()");
	} else {
		$ot->fijar("vesPopup", 0);
		$ot->fijar("onClose", "location.href='modlaboratorios.php'");	
	}

	echo $ot->Output();

}

function PaginaBasica() {
	//	AccionesSeleccion();
	ListarProveedores();
	OperacionesConProveedores();
}

function BorrarProveedor($id) {
	$oProveedor = new laboratorio;

	if ($oProveedor->Load($id)) {
		//$nombre = $oProveedor->get("Nombre");
		echo gas("Aviso", _("Laboratorio  borrado"));
		$oProveedor->MarcarEliminado();
	} else {
		echo gas("Aviso", _("No se ha podido borrar el laboratorio"));
	}
}

function AgnadirCarritoProveedores($id) {
	$actual = getSesionDato("CarritoProv");
	if (!is_array($actual)) {
		$actual = array ();
	}

	if (!in_array($id, $actual))
		array_push($actual, $id);

	$_SESSION["CarritoProv"] = $actual;
}

function ListarOpcionesSeleccion() {
	echo gas("titulo", _("Operaciones sobre la selección"));
	echo "<table border=1>";
	echo "<tr><td>"._("Hacer una compra a proveedores")."</td><td>".gModo("comprar", _("Comprar"))."</td></tr>";
	echo "<tr><td>"._("Buscar en el almacén")."</td><td>".gModo("transsel", _("Buscar"))."</td></tr>";
	//echo "<tr><td>"._("Cambio global de precio")."</td><td>".gModo("preciochange",_("Precios"))."</td></tr>";
	echo "</table>";
}

function ConvertirSelProveedores2Articulos() {
}


function FormularioDeCambiodePrecio() {
}
function proveedorEnAlmacen($id) {
}

function VaciarDatosProveedoresyAlmacen() {
	query("DELETE FROM ges_proveedores");
}

function CrearProveedor($comercial, $legal, $direccion, $poblacion,
	 $cp, $email, $telefono1, $telefono2, $contacto, $cargo, $cuentabancaria, $numero,
	 	 $comentario,$IdModPagoHabitual,$paginaweb,$idpais) {

	$oProveedor = new laboratorio;
	$oProveedor->Crea();


	$oProveedor->set("IdPais", $idpais, FORCE);
	$oProveedor->set("PaginaWeb", $paginaweb, FORCE);

	$oProveedor->set("NombreComercial", $comercial, FORCE);
	$oProveedor->set("NombreLegal", $legal, FORCE);
	$oProveedor->set("Direccion", $direccion, FORCE);
	$oProveedor->set("Localidad", $poblacion, FORCE);
	$oProveedor->set("CP", $cp, FORCE);
	$oProveedor->set("Email", $email, FORCE);
	$oProveedor->set("Telefono1", $telefono1, FORCE);
	$oProveedor->set("Telefono2", $telefono2, FORCE);
	$oProveedor->set("Contacto", $contacto, FORCE);
	$oProveedor->set("Cargo", $cargo, FORCE);	
	$oProveedor->set("CuentaBancaria", $cuentabancaria, FORCE);
	$oProveedor->set("NumeroFiscal", $numero, FORCE);
	$oProveedor->set("Comentarios", $comentario, FORCE);

	
	$oProveedor->set("IdModPagoHabitual", $IdModPagoHabitual, FORCE);

	if ($oProveedor->Alta()) {
		if(isVerbose())
			echo gas("aviso", _("Nuevo proveedor registrado"));
		return true;
	} else {
		if (isVerbose())
			echo gas("aviso", _("No se ha podido registrar el nuevo producto"));
		return false;
	}

}

function ModificarLaboratorio($id,$comercial, $legal, $direccion, $poblacion, $cp, $email, $telefono1, 
	$telefono2, $contacto, $cargo, $cuentabancaria, $numero, $comentario,	$IdModPagoHabitual,$paginaweb,$idpais	){

        $comercial = str_replace('&','&#038;',$comercial);
        $legal     = str_replace('&','&#038;',$legal);

	$oProveedor = new laboratorio;
	if (!$oProveedor->Load($id)){
		error(__FILE__ . __LINE__ ,"W: no pudo mostrareditar '$id'");
		return false;	
	}
	
	$oProveedor->set("IdPais", $idpais, FORCE);
	$oProveedor->set("PaginaWeb", $paginaweb, FORCE);
	
	$oProveedor->set("NombreComercial", $comercial, FORCE);
	$oProveedor->set("NombreLegal", $legal, FORCE);
	$oProveedor->set("Direccion", $direccion, FORCE);
	$oProveedor->set("Localidad", $poblacion, FORCE);
	$oProveedor->set("CP", $cp, FORCE);
	$oProveedor->set("Email", $email, FORCE);
	$oProveedor->set("Telefono1", $telefono1, FORCE);
	$oProveedor->set("Telefono2", $telefono2, FORCE);
	$oProveedor->set("Contacto", $contacto, FORCE);
	$oProveedor->set("Cargo", $cargo, FORCE);	
	$oProveedor->set("CuentaBancaria", $cuentabancaria, FORCE);
	$oProveedor->set("NumeroFiscal", $numero, FORCE);
	$oProveedor->set("Comentarios", $comentario, FORCE);
	
	if($IdModPagoHabitual)
		$oProveedor->set("IdModPagoHabitual", $IdModPagoHabitual, FORCE);

	
	if ($oProveedor->Modificacion() ){
		if(isVerbose())
			echo gas("aviso",_("Laboratorio modificado"));	
	} else {
		echo gas("problema",_("No se puede cambiar datos de [$comercial]"));	
	}	
}


PageStart();

$esPopup = false;

switch ($modo) {
	case "borrar":

		$Id = CleanID($_GET["Id"]);
		BorrarProveedor($Id);
		PaginaBasica();
		break;
	case "modproveedor":
		$id        = CleanID($_POST["IdProveedor"]);
		$comercial = CleanText($_POST["NombreComercial"]);
		$legal     = CleanText($_POST["NombreLegal"]);
		$direccion = CleanText($_POST["Direccion"]);
		$poblacion = CleanText($_POST["Localidad"]);
		$cp        = CleanCP($_POST["CP"]);
		$email     = CleanEmail($_POST["Email"]);
		$telefono1 = CleanText($_POST["Telefono1"]);
		$telefono2 = CleanText($_POST["Telefono2"]);
		$contacto  = CleanText($_POST["Contacto"]);
		$cargo     = CleanText($_POST["Cargo"]);
		$cuentabancaria = CleanCC($_POST["CuentaBancaria"]);
		$numero     = CleanText($_POST["NumeroFiscal"]);
		$comentario = CleanText($_POST["Comentarios"]);
		$IdModPagoHabitual = CleanID($_POST["IdModPagoHabitual"]);
		$paginaweb  = CleanUrl($_POST["PaginaWeb"]);
		$idpais     = CleanID($_POST["IdPais"]);
		
		ModificarLaboratorio($id,$comercial, $legal, $direccion, $poblacion, $cp, $email,
			 $telefono1, $telefono2, $contacto, $cargo, $cuentabancaria, $numero, $comentario,
			 	$IdModPagoHabitual,$paginaweb,$idpais);
		//Separador();
		PaginaBasica();
		break;
	case "editar":	
		$id = CleanID($_GET["Id"]);
		MostrarProveedorParaEdicion($id,$lang);		
		break;
	case "newproveedor" :
	        $comercial = CleanText($_POST["NombreComercial"]);
		$legal     = CleanText($_POST["NombreLegal"]);
		$direccion = CleanText($_POST["Direccion"]);
		$poblacion = CleanText($_POST["Localidad"]);
		$cp        = CleanCP($_POST["CP"]);
		$email     = CleanEmail($_POST["Email"]);
		$telefono1 = CleanText($_POST["Telefono1"]);
		$telefono2 = CleanText($_POST["Telefono2"]);
		$contacto  = CleanText($_POST["Contacto"]);
		$cargo     = CleanText($_POST["Cargo"]);
		$cuentabancaria = CleanCC($_POST["CuentaBancaria"]);
		$numero     = CleanText($_POST["NumeroFiscal"]);
		$comentario = CleanText($_POST["Comentarios"]);
		$IdModPagoHabitual = CleanID($_POST["IdModPagoHabitual"]);
		$paginaweb  = (isset($_POST["PaginaWeb"]))? CleanUrl($_POST["PaginaWeb"]):"";
		$idpais     = (isset($_POST["IdPais"]))? CleanID($_POST["IdPais"]):1;
		$espopup    = $_POST["esPopup"];

		if (CrearProveedor($comercial, $legal, $direccion, $poblacion, $cp, $email, $telefono1, $telefono2, $contacto, $cargo, $cuentabancaria, $numero, $comentario,$IdModPagoHabitual,$paginaweb,$idpais )) {
			if ($espopup){
				echo "<script>parent.closepopup()</script>";
				exit();				
			}
			//Separador();
			PaginaBasica();						
		} else {										
			FormularioAlta($espopup);
		}		
		break;
	case "altapopup":
	    $esPopup = true;
	case "alta" :
		FormularioAlta($esPopup);
		break;
	case "listar" :
		PaginaBasica();
		break;
	case "pagmenos":
		$indice = getSesionDato("PaginadorProv");
		$indice = $indice - $tamPagina;
		if ($indice<0)
			$indice = 0;
		setSesionDato("PaginadorProv",$indice);
		PaginaBasica();
		break;	
	case "pagmas":
		$indice = getSesionDato("PaginadorProv");
		$indice = $indice + $tamPagina;
		setSesionDato("PaginadorProv",$indice);
		PaginaBasica();
		break;			
	default :
		PaginaBasica();
		break;
}

PageEnd();
?>
