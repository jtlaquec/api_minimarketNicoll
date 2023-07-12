<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\Inventario;
use App\Models\VentaInventario;
use App\Models\Comprobante;
use App\Models\Serie;
use App\Models\Caja;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use FPDF;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;




class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $venta = Venta::where('estado',1)->get();
        $list = [];
        foreach($venta as $m){
            $list[] = $this->show($m);
        }
        return $list;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        try {
            $validator = Validator::make($request->all(), [
                'nombreCliente' => ['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'tipoDocumento' => ['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'numeroDocumento' => ['nullable', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s-]+$/'],
                'carrito' => ['required', 'array'],
                'carrito.*' => ['array'],
                'carrito.*.producto.id' => ['required', 'exists:productos,id'],
                'carrito.*.cantidad' => ['required','numeric','min:0',],
                'carrito.*.precio' => ['required', 'numeric', 'min:0'],
            ]);
        
            $validator->validate();
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], $e->status);
        }
        
        foreach($request->carrito as $m){
            $producto = $m['producto'];
            $producto = Producto::find($producto['id']);

            if ($producto->stock < $m['cantidad']){
                return response()->json(['error' => 'La cantidad ingresada es mayor que el stock disponible en el producto '.$producto->nombre], 400);
            }

        }

        $venta = new Venta();
        $venta->nombreCliente = $request->input('nombreCliente','Público en General');
        $venta->tipoDocumento = $request->input('tipoDocumento','DNI');
        $venta->numeroDocumento = $request->input('numeroDocumento','00000000');

        // Obtener la serie activa
        $serie = Serie::where('estado', 1)->first();

        if (isset($serie)) {
            // Obtener el último comprobante generado para esta serie
            $ultimoComprobante = Comprobante::where('serie_id', $serie->id)->latest()->first();

            if ($ultimoComprobante && $ultimoComprobante->numero === '099') {
                // Si el último comprobante alcanzó '099', se crea una nueva serie con el siguiente número de serie
                $serie->estado = 0; // Desactivar la serie actual
                $serie->save();

                // Obtener el último número de serie generado para calcular el siguiente número
                $ultimoNumeroSerie = intval(substr($serie->series, 1));
                $nuevoNumeroSerie = str_pad($ultimoNumeroSerie + 1, 3, '0', STR_PAD_LEFT);
                $nuevoNumeroSerie = 'B' . $nuevoNumeroSerie;

                // Crear una nueva serie con estado activo y el nuevo número de serie
                $nuevaSerie = new Serie();
                $nuevaSerie->series = $nuevoNumeroSerie;
                $nuevaSerie->estado = 1;
                $nuevaSerie->save();

                $serie = $nuevaSerie; // Usar la nueva serie para generar el código de comprobante
            }

            // Obtener el último número de comprobante generado para esta serie
            $ultimoNumeroComprobante = ($ultimoComprobante) ? intval($ultimoComprobante->numero) : 0;

            if ($ultimoNumeroComprobante === 99) {
                $numero = '001'; // Reiniciar el número de comprobante a '001'
            } else {
                // Generar el código de comprobante automáticamente
                $nuevoNumeroComprobante = str_pad($ultimoNumeroComprobante + 1, 3, '0', STR_PAD_LEFT);
                $numero = $nuevoNumeroComprobante;
            }

            // Crear un nuevo registro de comprobante
            $comprobante = new Comprobante();
            $comprobante->serie_id = $serie->id;
            $comprobante->numero = $numero;
            $comprobante->estado = 1;
            $comprobante->save();

            // Asignar el ID del comprobante al campo "comprobante_id" de la venta
            $venta->comprobante_id = $comprobante->id;
        }
        $venta->motivo = "Por la venta ".$serie->series."-".$numero;
        $venta->save();

        $total = 0;


        foreach($request->carrito as $m){
            $producto = $m['producto'];
            $inventario = new Inventario();
            $inventario->producto_id = $producto['id'];
            $producto = Producto::find($producto['id']);
            $inventario->tipo = 2;
            $inventario->costoUnitario = $producto->precioCompra;
            $inventario->cantidad = $m['cantidad'];
            $inventario->motivo = "Por la venta ".$serie->series."-".$numero;
            $inventario->save();
            $ventaInventario = new VentaInventario();
            $ventaInventario->inventario_id = $inventario->id;
            $ventaInventario->venta_id = $venta->id;
            $ventaInventario->cantidad = $m['cantidad'];
            $ventaInventario->precio = $m['precio'];
            $ventaInventario->save();

            //Modificar stock de tabla producto
           
            $producto->stock -= $m['cantidad'];
            $producto->save();
            $cantidad = $m['cantidad'];
            $precio = $m['precio'];
            $subtotal = $cantidad * $precio;
            $total += $subtotal;

        }


        $venta->montoPagoCliente = $total;
        $venta->save();

        $caja = new Caja();
        $caja->tipo = 2;
        $caja->fecha = $venta->created_at;
        $caja->venta_id = $venta->id;
        $caja->monto = $venta->montoPagoCliente;
        $caja->motivo = $venta->motivo;
        $caja->save();
        return $venta;
    }
    /**
     * Display the specified resource.
     */
    public function show(Venta $venta)
    {
        $venta->venta_inventarios = $venta->VentaInventarios()->with(['Inventario'=>function($i){
            $i->with(['Producto'=>function($a){
                $a->with(['Marca','Categoria','Medida']);
            }]);
        }])->get();
        $venta->fecha = $venta->created_at->format('Y-m-d');
        return $venta;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Venta $venta)
    {
        return response()->json(['message' => 'Metodo no implementado']);
    }

    /**
     * Remove the specified resource from storage.
     */

    public function pdf(Venta $venta)
    {
        $venta = $this->show($venta);
        $pdf = new FPDF();
        $pdf->AddPage('P', [80, 250]); // Ajustar el tamaño del papel según tu impresora térmica
        
        // Configuración del PDF
        $pdf->SetFont('Arial', '', 10);
        
        // Contenido del ticket
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('Arial','B',10);
        $pdf->MultiCell(0,5,utf8_decode(strtoupper("MINIMARKET NICOLL")),0,'C',false);
        $pdf->SetFont('Arial','',9);
        
        $pdf->MultiCell(0,5,utf8_decode("RUC: 10004050661"),0,'C',false);
        $pdf->MultiCell(0,5,utf8_decode("Asoc. 28 de agosto Mz. 310 Lt. 7 Cmt. 4 - Ciudad Nueva - Tacna - Tacna"),0,'C',false);
        
    
        $pdf->Ln(1);
        $pdf->Cell(0,5,utf8_decode("------------------------------------------------------"),0,0,'C');
        $pdf->Ln(5);
    
        $pdf->MultiCell(0, 5, utf8_decode("Fecha: " . date("d/m/Y h:i A", strtotime($venta->created_at))), 0, 'C', false);
        $pdf->SetFont('Arial','B',8);
        $pdf->MultiCell(0,5,utf8_decode(strtoupper("BOLETA DE VENTA ELECTRÓNICA ".$venta->comprobante->serie->series."-".$venta->comprobante->numero)),0,'C',false);
        $pdf->SetFont('Arial','',9);
    
        $pdf->Ln(1);
        $pdf->Cell(0,5,utf8_decode("------------------------------------------------------"),0,0,'C');
        $pdf->Ln(5);

        $pdf->MultiCell(0,5,utf8_decode("Documento: ".$venta->tipoDocumento." ".$venta->numeroDocumento),0,'C',false);
        $pdf->MultiCell(0,5,utf8_decode("Nombre: ".$venta->nombreCliente),0,'C',false);
    
        $pdf->Ln(1);
        $pdf->Cell(0,5,utf8_decode("------------------------------------------------------"),0,0,'C');
        $pdf->Ln(5);
        
        $pdf->Ln(1);
        $pdf->Cell(0,5,utf8_decode("-------------------------------------------------------------------"),0,0,'C');
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 9);
        # Tabla de productos #
        
        $pdf->Cell(1,5,utf8_decode("Cant."),0,0,'C');
        $pdf->Cell(38,5,utf8_decode("P.U."),0,0,'C');
        $pdf->Cell(-3,5,utf8_decode("Descripción"),0,0,'C');
        $pdf->Cell(43,5,utf8_decode("Total"),0,0,'C');
        $pdf->SetFont('Arial','', 9);
        $pdf->Ln(3);
        $pdf->Cell(0,5,utf8_decode("-------------------------------------------------------------------"),0,0,'C');
        $pdf->Ln(3);
        
    
        $Total = 0;
        /*----------  Detalles de la tabla  ----------*/
        foreach ($venta->venta_inventarios as $d){
/*             $pdf->Cell(1,5,utf8_decode($d->cantidad),0,0,'C');
            $pdf->Cell(18, 5, utf8_decode($d->inventario->producto->medida->codigo), 0, 0, 'C');
            $pdf->Cell(5,5,utf8_decode($d->precio),0,0,'C');
            $Total += $d->cantidad * $d->precio;
            $pdf->Cell(25,5,utf8_decode($d->inventario->producto->nombre),0,0,'C');
            $pdf->Cell(20, 5, utf8_decode(number_format($d->cantidad * $d->precio, 2)), 0, 0, 'C');
            $pdf->Ln(5); */

            $pdf->Cell(1, 5, utf8_decode($d->cantidad), 0, 0, 'C');
            $pdf->Cell(18, 5, utf8_decode($d->inventario->producto->medida->codigo), 0, 0, 'C');
            $pdf->Cell(5, 5, utf8_decode($d->precio), 0, 0, 'C');
            $Total += $d->cantidad * $d->precio;
            
            // Guardar la posición actual del cursor
            $pdf->Cell(25, 5, $d->inventario->producto->nombre, 0, 0, 'C');

            $pdf->Cell(20, 5, utf8_decode(number_format($d->cantidad * $d->precio, 2)), 0, 0, 'C');
            
            // Mover a la siguiente línea
            $pdf->Ln(5);


        }
        $igv = $Total * 0.18;
        $subTotal = $Total - $igv;
       
        /*----------  Fin Detalles de la tabla  ----------*/
    
    
    
        $pdf->Cell(0,5,utf8_decode("-------------------------------------------------------------------"),0,0,'C');
    
        $pdf->Ln(5);
        # Impuestos & totales #
        $pdf->Cell(18,5,utf8_decode(""),0,0,'C');
        $pdf->Cell(22,5,utf8_decode("SUBTOTAL"),0,0,'C');
        $pdf->Cell(32,5,utf8_decode("S/ ". number_format($subTotal,2)),0,0,'C');
    
        $pdf->Ln(5);
    
        $pdf->Cell(18,5,utf8_decode(""),0,0,'C');
        $pdf->Cell(22,5,utf8_decode("IGV (18%)"),0,0,'C');
        $pdf->Cell(32,5,utf8_decode("S/ ". number_format($igv,2)),0,0,'C');
    
        $pdf->Ln(5);
    
        $pdf->Cell(0,5,utf8_decode("-------------------------------------------------------------------"),0,0,'C');
    
        $pdf->Ln(5);
    
        $pdf->Cell(18,5,utf8_decode(""),0,0,'C');
        $pdf->Cell(22,5,utf8_decode("TOTAL A PAGAR"),0,0,'C');
        $pdf->Cell(32,5,utf8_decode("S/ ". number_format($Total,2)),0,0,'C');
        $pdf->Ln(5);
        $pdf->Cell(0,5,utf8_decode("-------------------------------------------------------------------"),0,0,'C');
    
        
        $pdf->Ln(5);
    
        $pdf->MultiCell(0,5,utf8_decode("*** Representación impresa de la boleta de venta electrónica ***"),0,'C',false);
    
        $pdf->SetFont('Arial','B',9);
        $pdf->Cell(0,7,utf8_decode("Gracias por su compra"),'',0,'C');
    
        # Nombre del archivo PDF #
        /* $pdf->Output("I","Ticket_Nro_1.pdf",true); */
                // Generar el contenido del PDF
        $pdfContent = $pdf->Output("S");
        
        // Generar la respuesta con el contenido del PDF
        $response = Response::make($pdfContent);
        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'inline; filename="Boleta de Venta Electrónica '.$venta->comprobante->serie->series."-".$venta->comprobante->numero.'"');
        
        return $response;

    }

    public function destroy(Venta $venta)
    {
        $venta->estado = 0;
        $venta->save();


        $caja = $venta->Caja;
        $caja->estado = 0;
 
        // Obtener los registros de venta_inventarios relacionados con la venta
        $ventaInventarios = $venta->VentaInventarios;
    
        foreach ($ventaInventarios as $ventaInventario) {
            $ventaInventario->estado = 0;
            $ventaInventario->save();
    
            // Obtener el inventario relacionado
            $inventario = $ventaInventario->Inventario;
            $inventario->estado = 0;
            $inventario->save();
        }
        return response()->json(['message' => 'Venta eliminada']);
    }
}
