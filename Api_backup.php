<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * @property CatalogoModel $catalogoModel
 * @property CarritoModel $carritoModel
 * @property CI_Form_validation $form_validation
 * @property Apimodel $Apimodel
 * @property OrdenModel $OrdenModel
 * @property Commonmodel $Commonmodel
 * @property HistoryStatusOrderModel $HistoryStatusOrderModel
 * @property MY_Model $mymodel
 */
class Api extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Apimodel');
        $this->load->model('CarritoModel');
        $this->load->model('CatalogoModel');
        $this->load->model('OrdenModel');
        $this->load->helper('url');
        $this->load->library('email');
        $this->load->library('form_validation');
        $this->load->model('HistoryStatusOrderModel');
        require 'vendor/autoload.php';
        error_reporting(0);
    }

    public function updateinv_post() {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {

        }

          $itemList = $userData['itemList'];  
          $company_id = $userData['company_id'];  

          $this->db->query("update products set stock=0 where company_id=".$company_id);

          foreach ($itemList as $ind => $val) {
                $mydata1 = array(
                'stock' => $val['stock'],
                'fob_price' => $val['precio'],
                'purchase_price' => $val['precio']
                );

                $where = "sku='".$val['sku']."' AND company_id='".$company_id."'";
                $update = $this->Apimodel->update_cond('products', $where, $mydata1);
          }

       $this->response([
            'status' => "1",
            'message' => 'Inventory Updated',
            'company' => $company_id,
            'list' => $itemList
        ], 200);

    }

     public function updateprecios_post() {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {

        }

          $itemList = $userData['itemList'];  
          $company_id = $userData['company_id'];  

          $this->db->query("update products set stock=0 where company_id=".$company_id);

          foreach ($itemList as $ind => $val) {
                $mydata1 = array(
                'fob_price' => $val['cost_price'],
                'purchase_price' => $val['cost_afx']
                );

                $where = "sku='".$val['sku']."' AND company_id='".$company_id."'";
                $update = $this->Apimodel->update_cond('products', $where, $mydata1);
          }

       $this->response([
            'status' => "1",
            'message' => 'Costs Updated',
            'company' => $company_id,
            'list' => $itemList
        ], 200);

    }

    public function updatehistoria_post() {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {

        }

          $itemList = $userData['itemList'];  
          $company_id = $userData['company_id'];  
          $ano = $userData['ano_id'];  

         $this->db->query("delete from costos_erp where company_id=".$company_id);
          
          
          try {
          foreach ($itemList as $ind => $val) {
                $mydata = array(
                'company_id' => $company_id,
                'ano_id' => $ano,
                'fecha' => $val['fecha'],
                'invId' => $val['invId'],
                'invRef' => $val['invRef'],
                'invNumber' => $val['invNumber'],
                'VendorName' => $val['VendorName'],
                'sku' => $val['sku'],
                'cost_price' => $val['cost_price'],
                'cost_afx' => $val['cost_afx']
                );

                $this->Apimodel->add_details('costos_erp', $mydata);
          }  
          } catch (Exception $e) {
            // Handle the exception if needed
            $this->response([
            'status' => "0",
            'message' => 'Error occurred while updating history: ' . $e->getMessage(),
            'company' => $company_id,
            'list' => $itemList
        ], 200);     
          }

          

       $this->response([
            'status' => "1",
            'message' => 'Costs Updated',
            'company' => $company_id,
            'list' => $itemList
        ], 200);

    }

    public function companyList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {

        }


        $consulta = "SELECT * FROM companies";
        $list = $this->Apimodel->fetch_all_join($consulta);

        foreach ($list as $c) {
            $lista[] = array(
                'company_id' => $c->company_id,
                'category_id' => $c->category_id,
                'language_id' => $c->language,
                'name' => $c->name,
                'short_name' => $c->short_name,
                'email' => $c->email,
                'password' => $c->password,
                'phone_number' => $c->phone_number,
                'cell_number' => $c->cell_number,
                'fax' => $c->fax,
                'image' => $c->image,
                'address' => $c->address,
                'country' => $c->country,
                'state' => $c->state,
                'city' => $c->city,
                'zip_code' => $c->zip_code,
                'tax_condition' => $c->tax_condition,
                'tax_id' => $c->tax_id,
                'max_number_of_clients' => $c->max_number_of_clients,
                'max_number_of_orders' => $c->max_number_of_orders,
                'max_number_of_vendors' => $c->max_number_of_vendors,
                'total_price' => $c->total_price,
                'website' => $c->website,
                'created' => $c->created,
                'status' => $c->status,
                'slug_url' => $c->slug_url,
                'currency' => $c->currency,
                'createdby_comp_id' => $c->createdby_comp_id,
                'currency_code_id' => $c->currency_code_id,
                'token' => $c->token,
                'ordersend_time' => $c->ordersend_time,
                'catalogsend_time' => $c->catalogsend_time,
                'format_id' => $c->format_id,
                'deleted_at' => $c->deleted_at,
                'sell_with_inventory' => $c->sell_with_inventory
            );
        }

        $lista = $this->arrcheck($lista);


        $this->response([
            'status' => "1",
            'message' => 'Companies Listed',
            'list' => $lista
        ], 200);


    }

    public function catalogLink_post() {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');        
        }

        $consulta = "select c.* from customers a left join (select MAX(cart_id) as cart_id, customer_id from catalog_cart group by customer_id) b on b.customer_id = a.customer_id left join (select * from catalog_cart) c on c.cart_id = b.cart_id where a.company_id = ".$userData['company_id']." and ifnull(b.cart_id,0)>0";

        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);

    }

    public function getCatalogLink_post() {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['customer_id'] = $this->post('customer_id');
        }

        $consulta = "select c.* from customers a left join (select MAX(cart_id) as cart_id, customer_id from catalog_cart group by customer_id) b on b.customer_id = a.customer_id left join (select * from catalog_cart) c on c.cart_id = b.cart_id where a.customer_id = ".$userData['customer_id']." and ifnull(b.cart_id,0)>0";

        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);

    }

    public function categoriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT cat_id, cat_id as category_id, language_id,company_id,category_name,images,category_status,category_created_at,id_kor,ifnull(category_default,0) as category_default  FROM categories Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

     public function costoserp_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['sku'] = $this->post('sku');
        }



       $consulta = "SELECT * from costos_erp Where company_id = " . $userData['company_id'] . " AND sku = '" . $userData['sku'] . "' ORDER BY fecha DESC";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function wcategoriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM categories Where company_id = " . $userData['company_id'] . " AND category_created_at BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE();";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function softproductList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM products Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


public function updateDeliveredItem_post() 
{
    $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['product_id'] = $this->post('product_id');
            $userData['delivered_qty'] = $this->post('delivered_qty');
            $userData['delivered_pack'] = $this->post('delivered_pack');
        }


         $mydata1 = array(
            'delivered_quantity' => $userData['delivered_qty'],
            'delivered_pack' => $userData['delivered_pack']
            );



        $where = "order_id=".$userData['order_id']." AND product_id=".$userData['product_id'];
        $update = $this->Apimodel->update_cond('order_details', $where, $mydata1);

        $delete = $this->Apimodel->delete_single_con('order_dispached', $where);

        $insert = $this->Apimodel->add_details('order_dispached', array(
            'order_id' => $userData['order_id'],
            'product_id' => $userData['product_id'],
            'delivered_qty' => $userData['delivered_qty'],
            'delivered_pack' => $userData['delivered_pack']
        ));
         

         $this->response([
            'status' => "1",
            'message' => 'Updated successfully',
            'order_id' => $userData['order_id'],
            'product_id' => $userData['product_id'],
            'list' => $mydata1,
            'where' => $where,
            'update' => $update        
        ], 200);


}

public function setstatus_post() 
{
    $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['status'] = $this->post('status');
        }

        if ($userData['status'] == "10") {
            $userData['status'] = "9";
        }

         $mydata1 = array(
            'order_status' => $userData['status']
            );

        $where = "order_id=".$userData['order_id'];
        $update = $this->Apimodel->update_cond('orders', $where, $mydata1);

         $this->response([
            'status' => "1",
            'message' => 'Updated successfully',
            'order_id' => $userData['order_id'],
            'list' => $mydata1,
            'where' => $where,
            'update' => $update        
        ], 200);


}

    public function updateDelivered_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['itemList'] = $this->post('itemList');
        }

        $itemList = $userData['itemList'];

         $where = array(
                    'order_id' => $userData['order_id']
          );
         $delete = $this->Apimodel->delete_single_con('bwareinfo', $where);

        $data = array();

        foreach ($itemList as $ind => $val) {
            // workarround to fix the existing difference between comment name on app with the column name
            //$itemList[$ind] = $this->prepareOrderDetail($itemList[$ind]);
            

            $mydata = array(
            'order_id' => $val['order_id'],
            'company_id' => $val['company_id'],
            'product_id' => $val['product_id'],
            'delivered_qty' => $val['delivered_qty'],
            'delivered_pack' => $val['delivered_pack']
            );

            $mydata1 = array(
            'delivered_quantity' => $val['delivered_qty'],
            'delivered_pack' => $val['delivered_pack']
            );
     
            $this->Apimodel->add_details("bwareinfo", $mydata);

            $where = "order_id=".$val['order_id']." AND product_id=".$val['product_id'];
            $update = $this->Apimodel->update_cond('order_details', $where, $mydata1);

            $delete = $this->Apimodel->delete_single_con('order_dispached', $where);

            $insert = $this->Apimodel->add_details('order_dispached', array(
                'order_id' => $userData['order_id'],
                'product_id' => $userData['product_id'],
                'delivered_qty' => $userData['delivered_qty'],
                'delivered_pack' => $userData['delivered_pack']
            ));


            $data[] = $mydata;

        }

        

        $this->response([
            'status' => "1",
            'message' => 'Updated successfully',
            'order_id' => $userData['order_id'],
            'company_id' => $userData['company_id'],
            'list' => $userData['itemList'],
            'data' => $data,
        ], 200);


    }

    public function softproductListactive_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM products p Where p.company_id = " . $userData['company_id'] . " and p.status=1";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function prodwithoutList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT
							a.company_id,
							a.pro_id,
							ifnull(b.img,0) AS img
							FROM products a
							LEFT JOIN (SELECT COUNT(img_id) AS img, product_id FROM product_images GROUP BY product_id) b ON b.product_id = a.pro_id
							WHERE a.company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function softproductList2_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM products Where company_id = " . $userData['company_id'] . " AND status = 1";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function sellers_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM vendors";
        $consulta1 = "SELECT * FROM warehouse_users";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM vendors Where company_id = " . $userData['company_id'];
            $consulta1 = "SELECT * FROM warehouse_users Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);
        $list1 = $this->Apimodel->fetch_all_join($consulta1);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        foreach ($list1 as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function insertUpdate_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['fecha'] = $this->post('fecha');
            $userData['updatenumber'] = $this->post('updatenumber');
        }

        $mydata = array(
            'fecha' => $userData['fecha'],
            'company_id' => $userData['company_id'],
            'updatenumber' => $userData['updatenumber']
        );

        $this->Apimodel->add_details("app_updates", $mydata);

        $this->response([
            'status' => "1",
            'message' => 'Inserted successfully'
        ], 200);


    }


    public function updateEmailCustomer_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['customer_id'] = $this->post('customer_id');
            $userData['newEmail'] = $this->post('newEmail');
        }

        $mydata = array(
            'email' => $userData['newEmail']
        );

        $consulta = "SELECT * FROM customers Where company_id ='" . $userData['company_id']."' AND email = '" .$userData['newEmail'] . "'";

        $list = $this->Apimodel->fetch_all_join($consulta);

        if (!empty($list)) {
            $this->response([
                'status' => "0",
                'message' => 'Email Exists'
            ], 200);

        } else {

            $where = "customer_id=".$userData['customer_id'];
            $update = $this->Apimodel->update_cond('customers', $where, $mydata);

            $this->response([
                'status' => "1",
                'message' => 'Email Updated'
            ], 200);
        }

       // $this->Apimodel->add_details("app_updates", $mydata);




    }


    public function gpstracker_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['longitude'] = $this->post('longitude');
            $userData['latitude'] = $this->post('latitude');
            $userData['login'] = $this->post('login');
            $userData['deviceinfo'] = $this->post('deviceinfo');
        }

        $mydata = array(
            'company_id' => $userData['company_id'],
            'user_id' => $userData['user_id'],
            'longitude' => $userData['longitude'],
            'latitude' => $userData['latitude'],
            'login' => $userData['login'],
            'deviceinfo' => $userData['deviceinfo']
        );

        $this->Apimodel->add_details("gpstracker", $mydata);

        $this->response([
            'status' => "1",
            'message' => 'Inserted successfully'
        ], 200);

    }


    public function changesapp_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['email'] = $this->post('email');
        }

        $mydata = array(
            'company_id' => $userData['company_id'],
            'user_id' => $userData['user_id'],
            'email' => $userData['email']
        );

        $this->Apimodel->add_details("changes_app", $mydata);

        $this->response([
            'status' => "1",
            'message' => 'Inserted successfully'
        ], 200);

    }


    public function gpslog_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['login'] = $this->post('login');
            $userData['textinfo'] = $this->post('textinfo');
        }

        $mydata = array(
            'company_id' => $userData['company_id'],
            'user_id' => $userData['user_id'],
            'login' => $userData['login'],
            'infotext' => $userData['textinfo']
        );

        $this->Apimodel->add_details("gpslog", $mydata);

        $this->response([
            'status' => "1",
            'message' => 'Inserted successfully'
        ], 200);

    }


    public function catalogList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM catalog";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM catalog Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function getCategoryDefault_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM categories Where category_name ='Without Category'";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM categories Where category_name ='Without Category' AND company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function proList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM products";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM products Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        //$data = $this->arrcheck($data);

        // Convertir a JSON
        $json = json_encode($data);

        // Imprimir el JSON
        echo $json;


    }


    public function citiesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM cities";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function companiescategoriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM companies_categories";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function companycontactsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM company_contacts";

        if ($userData['company_id'] != null) {
            $consulta = "SELECT * FROM company_contacts Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function countriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM countries";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function currencyList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM currency ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function currencybycountriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM currencybycountries ";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function currencyformatsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM currency_formats ";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function customersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        //$consulta = "SELECT * FROM customers ";
        $consulta ="SELECT a.*, b.name as group_name, b.percentage_on_price, b.percent_price_amount  FROM customers a LEFT JOIN customer_groups b on b.group_id = a.group_id ";
        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where a.company_id = " . $userData['company_id'];
        }
        $consulta = $consulta . " ORDER BY TRIM(a.name) ASC";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                if ($nombre_campo == "catalog_emails") {
                    if ($valor == null || $valor == "") {
                        $valor = "0";
                    }
                }
                $tmpvalor = $valor;

                $fila_array[$nombre_campo] = $tmpvalor;

            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function wcustomersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM customers ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id']; //." AND modified_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND modified_at <= CURDATE()";
        }
        $consulta = $consulta . " ORDER BY TRIM(name) ASC";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                if ($nombre_campo == "catalog_emails") {
                    if ($valor == null || $valor == "") {
                        $valor = "0";
                    }
                }
                $tmpvalor = $valor;

                $fila_array[$nombre_campo] = $tmpvalor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function customergroupsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM customer_groups ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function ordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM orders ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'] . " AND updated >= CURDATE() - INTERVAL 7 DAY";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function wordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM orders ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'] . " AND updated >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  			AND updated < CURDATE();";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function requestedList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM rs_requested ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function ordrerscategoriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM orders_categories ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function ordercategoriesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM order_categories ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function orderdetailList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
        }


        $consulta = "SELECT * FROM order_details ";

        if ($userData['order_id'] != null) {
            $consulta = $consulta . " Where order_id = " . $userData['order_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function productsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM products ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function getUpdateApplication_post()
    {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['date_id'] = $this->post('date_id');
        }

        $company_id = $userData['company_id'];
        $user_id = $userData['user_id'];
        $date_id = $userData['date_id'];


        $respuesta = "Micro";

        $fecha1 = new DateTime($date_id);
        $fecha2 = new DateTime();


        // Calcular la diferencia
        //$diferencia = $fecha1->diff($fecha2);

        ///if ($diferencia->days > 7) {
           // $respuesta = "Macro";
        //}

        $this->response([
            'status' => "1",
            'Update' =>  $respuesta,
            'lastDate' => $fecha1,
            'company_id' => $company_id,
            'days' => 7 //$diferencia->days
        ], 200);


    }


    public function getUpdateCadenaAPI_post()
    {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['date_id'] = $this->post('date_id');
        }

        $company_id = $userData['company_id'];
        $user_id = $userData['user_id'];
        $date_id = $userData['date_id'];

        $fecha1 = new DateTime($date_id);

        $data = array();
        $data1 = array();
        $data2 = array();
        $data3 = array();
        $data4 = array();
        $data5 = array();

        // Customers
        $consulta = "SELECT * FROM customers ";

        $consulta = $consulta . " Where company_id = " . $userData['company_id'];//." AND modified_at >'" .$fecha1."'";


        $list = $this->Apimodel->fetch_all_join($consulta);

        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }


        // categories
        $consulta1 = "SELECT * FROM categories ";

        $consulta1 = $consulta1 . " Where company_id = " . $userData['company_id'];//." AND modified_at >'" .$fecha1."'";


        $list1 = $this->Apimodel->fetch_all_join($consulta1);

        foreach ($list1 as $fila1) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila1 as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data1[] = $fila_array;
        }


        $data = $this->arrcheck($data);
        $data1 = $this->arrcheck($data1);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'updateType' => 'Micro',
            'lastDate' => $date_id,
            'company_id' => $company_id,
            'customerlist' => $data,
            'categorieslist' => $data1
        ], 200);

    }

    public function productimagesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $company_id = $userData['company_id'];

        $consulta = "SELECT a.*, b.sku as skup FROM product_images a
        INNER JOIN products b ON b.pro_id = a.product_id ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where b.status = 1 AND a.company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            //$fila_array = array();

            // Recorrer cada columna de la fila
            /*foreach ($fila as $nombre_campo => $valor) {
				// Añadir el valor al array asociativo usando el nombre del campo como clave
				$fila_array[$nombre_campo] = $valor;
			}*/

            //$fila_array = {

            //};
            $tam = 0;
            $rutaCompleta = "";
            $url = "";
            $nombreArchivo = "";
            if (!empty($fila->images)) {

                $nombreArchivo = $_SERVER['DOCUMENT_ROOT'] . "/uploads/products/" . $company_id . "/" . $fila->images;
                $url = base_url() . 'uploads/products/' . $company_id . '/' . $fila->images;
                $rutaCompleta = __DIR__ . $nombreArchivo; // Ruta completa al archivo


                if (file_exists($nombreArchivo)) {
                    $tama = filesize($nombreArchivo);
                    $tam = $tama; // Tamaño en KB
                    $tamMB = $tam / 1024; // Tamaño en MB
                } else {
                }

            }

            // Añadir el array de esta fila al array de datos
            $data[] = array(
                'img_id' => $fila->img_id,
                'product_id' => $fila->product_id,
                'language_id' => $fila->language_id,
                'images' => $fila->images,
                'created' => $fila->created,
                'updated' => $fila->updated,
                'company_id' => $fila->company_id,
                'sku' => $fila->skup,
                'orderImg' => $fila->img_order,
                'url' => $url,
                'FileSize' => $tam
            );           //$fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function sproductimagesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $company_id = $userData['company_id'];

        $consulta = "SELECT a.*, b.sku as skup FROM product_images a
        INNER JOIN products b ON b.pro_id = a.product_id ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where  a.company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            //$fila_array = array();

            // Recorrer cada columna de la fila
            /*foreach ($fila as $nombre_campo => $valor) {
				// Añadir el valor al array asociativo usando el nombre del campo como clave
				$fila_array[$nombre_campo] = $valor;
			}*/

            //$fila_array = {

            //};
            $tam = 0;
            $rutaCompleta = "";
            $url = "";
            $nombreArchivo = "";
            if (!empty($fila->images)) {

                $nombreArchivo = $_SERVER['DOCUMENT_ROOT'] . "/uploads/products/" . $company_id . "/" . $fila->images;
                $url = base_url() . 'uploads/products/' . $company_id . '/' . $fila->images;
                $rutaCompleta = __DIR__ . $nombreArchivo; // Ruta completa al archivo


                if (file_exists($nombreArchivo)) {
                    $tama = filesize($nombreArchivo);
                    $tam = $tama; // Tamaño en KB
                    $tamMB = $tam / 1024; // Tamaño en MB
                } else {
                }

            }

            // Añadir el array de esta fila al array de datos
            $data[] = array(
                'img_id' => $fila->img_id,
                'product_id' => $fila->product_id,
                'language_id' => $fila->language_id,
                'images' => $fila->images,
                'created' => $fila->created,
                'updated' => $fila->updated,
                'company_id' => $fila->company_id,
                'sku' => $fila->skup,
                'orderImg' => $fila->img_order,
                'url' => $url,
                'FileSize' => $tam
            );           //$fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }


    public function checkVersion_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['deviceinfo'] = $this->post('deviceinfo');
        }

        $deviceinfo = $userData['deviceinfo'];

        $consulta = "SELECT * FROM deviceinfos WHERE deviceinfo = '$deviceinfo' LIMIT 1";


        $list = $this->Apimodel->fetch_all_join($consulta);

        if (empty($list)) {
            $mydata = array(
                'deviceinfo' => $deviceinfo,
                'so' => 'Android',
                'version' => '0',
                'versionname' => '0.0.0'
            );

            $this->Apimodel->add_details("deviceinfos", $mydata);

        }

        $list = $this->Apimodel->fetch_all_join($consulta);

        foreach ($list as $fila) {
            // Añadir el array de esta fila al array de datos
            $data [] = array(
                'deviceinfo' => $fila->deviceinfo,
                'so' => $fila->so,
                'version' => $fila->version,
                'versionname' => $fila->versionname
            );
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }


    public function APKUpdateVersion_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['deviceinfo'] = $this->post('deviceinfo');
            $userData['so'] = $this->post('so');
            $userData['version'] = $this->post('version');
            $userData['versionname'] = $this->post('versionname');
        }

        $deviceinfo = $userData['deviceinfo'];
        $so = $userData['so'];
        $version = $userData['version'];
        $versionname = $userData['versionname'];

        $consulta = "SELECT * FROM deviceinfos WHERE deviceinfo = '$deviceinfo' LIMIT 1";

        $list = $this->Apimodel->fetch_all_join($consulta);

        if (!empty($list)) {

            $mydata = array(
                'deviceinfo' => $deviceinfo,
                'so' => $so,
                'version' => $version,
                'versionname' => $versionname
            );

            $where = "deviceinfo='$deviceinfo'";
            $update = $this->Apimodel->update_cond('deviceinfos', $where, $mydata);
        }

        $list = $this->Apimodel->fetch_all_join($consulta);

        foreach ($list as $fila) {
            // Añadir el array de esta fila al array de datos
            $data [] = array(
                'deviceinfo' => $fila->deviceinfo,
                'so' => $fila->so,
                'version' => $fila->version,
                'versionname' => $fila->versionname
            );
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }


    public function syncimagesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $company_id = $userData['company_id'];

		//$consulta = "SELECT * FROM product_images WHERE updated >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
		$consulta = "SELECT sec.* FROM (SELECT product_id FROM product_images WHERE company_id = " .$userData['company_id']. " AND updated >= DATE_SUB(CURDATE(), INTERVAL -1 DAY) GROUP BY product_id ) pimg INNER JOIN product_images sec ON sec.product_id = pimg.product_id";

       // if ($userData['company_id'] != null) {
            //$consulta = $consulta . " AND sec.company_id = " . $userData['company_id'];
        //}


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        if (empty($list)) {
            $data[] = array(
                'img_id' => "0",
                'product_id' => "0",
                'language_id' => "0",
                'images' => "noimg.png",
                'created' => "",
                'updated' => "",
                'company_id' => "0",
                'sku' => "0",
                'url' => "https://app.fullvendor.com/uploads/noimg.png",
                'FileSize' => "37",
                );
                $data = $this->arrcheck($data);

		        echo json_encode($data);         //$fila_array;
        } else {

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            //$fila_array = array();

            // Recorrer cada columna de la fila
            /*foreach ($fila as $nombre_campo => $valor) {
				// Añadir el valor al array asociativo usando el nombre del campo como clave
				$fila_array[$nombre_campo] = $valor;
			}*/

            //$fila_array = {

            //};
            $tam = 0;
            $rutaCompleta = "";
            $url = "";
            $nombreArchivo = "";
            if (!empty($fila->images)) {

                $nombreArchivo = $_SERVER['DOCUMENT_ROOT'] . "/uploads/products/" . $company_id . "/" . $fila->images;
                $url = base_url() . 'uploads/products/' . $company_id . '/' . $fila->images;
                $rutaCompleta = __DIR__ . $nombreArchivo; // Ruta completa al archivo



				if (file_exists($nombreArchivo)) {
					$tama = filesize($nombreArchivo);
					$tam = $tama; // Tamaño en KB
					$tamMB = $tam / 1024; // Tamaño en MB
				} else {
				}

			}

			// Añadir el array de esta fila al array de datos
			$data[] = array(
			'img_id' => $fila->img_id,
			'product_id' => $fila->product_id,
			'language_id' => $fila->language_id,
			'images' => $fila->images,
			'created' => $fila->created,
			'updated' => $fila->updated,
			'company_id' => $fila->company_id,
			'sku' => $fila->sku,
			'url' => $url,
			'FileSize' => $tam
			);           //$fila_array;
		}

		$data = $this->arrcheck($data);

		echo json_encode($data);
       }

	}

	public function syncAllimagesList_post()
	{
		$json = file_get_contents('php://input');
		$obj = json_decode($json,true);

		if(is_array($obj)) {
			$_POST = (array) $obj;
			$userData = $_POST;
		} else {
			$userData['company_id'] = $this->post('company_id');
		}

		$company_id = $userData['company_id'];

		$consulta = "SELECT * FROM product_images ";
		//$consulta = "SELECT sec.* FROM (SELECT product_id FROM product_images WHERE updated >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY product_id ) pimg INNER JOIN product_images sec ON sec.product_id = pimg.product_id";

		if ($userData['company_id'] != null) {
			$consulta = $consulta . " WHERE company_id = ".$userData['company_id'];
		}




		$list = $this->Apimodel->fetch_all_join($consulta);

		$data = array();

		// Recorrer cada fila de los resultados
		foreach ($list as $fila) {
			// Array para almacenar los valores de esta fila
			//$fila_array = array();

			// Recorrer cada columna de la fila
			/*foreach ($fila as $nombre_campo => $valor) {
				// Añadir el valor al array asociativo usando el nombre del campo como clave
				$fila_array[$nombre_campo] = $valor;
			}*/

			//$fila_array = {

			//};
		    $tam = 0;
			$rutaCompleta = "";
			$url = "";
			$nombreArchivo = "";
			if (!empty($fila->images)){

				$nombreArchivo = $_SERVER['DOCUMENT_ROOT'] ."/uploads/products/".$company_id."/".$fila->images;
				$url =base_url().'uploads/products/'.$company_id.'/' .$fila->images;
				$rutaCompleta = __DIR__ . $nombreArchivo; // Ruta completa al archivo



                if (file_exists($nombreArchivo)) {
                    $tama = filesize($nombreArchivo);
                    $tam = $tama; // Tamaño en KB
                    $tamMB = $tam / 1024; // Tamaño en MB
                } else {
                }

            }

            // Añadir el array de esta fila al array de datos
            $data[] = array(
                'img_id' => $fila->img_id,
                'product_id' => $fila->product_id,
                'language_id' => $fila->language_id,
                'images' => $fila->images,
                'created' => $fila->created,
                'updated' => $fila->updated,
                'company_id' => $fila->company_id,
                'sku' => $fila->sku,
                'url' => $url,
                'FileSize' => $tam
            );           //$fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function statesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['country_id'] = $this->post('country_id');
        }


        $consulta = "SELECT * FROM states ";

        if ($userData['country_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['country_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function statusordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM status_orders ";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }


    public function termofsalesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM terms_of_sales ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function unitsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM units ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function vendorsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM vendors ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function gpsdictionaryList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);


        $consulta = "SELECT * FROM dictionary_gps ";


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function gpsuserList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM vendors ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila

            $pic = base_url() . 'images/no_image_user.png';

            if ($fila->profile_image != "") {
                $pic = base_url() . 'uploads/users/' . $fila->profile_image;
            } else {
                $pic = base_url() . 'images/no_image_user.png';
            }

            $lng = 0.00;
            $lat = 0.00;
            $address = "";
            $lastdate = "";
            $id = 0;

            $consulta2 = "SELECT id,created, longitude, latitude, address FROM gpstracker WHERE user_id = " . $fila->user_id . " and login = 1 order by id desc limit 1";

            $list3 = $this->Apimodel->fetch_all_join($consulta2);

            foreach ($list3 as $l3) {
                $lng = $l3->longitude;
                $lat = $l3->latitude;
                $address = $l3->address;
                $id = $l3->id;
                $lastdate = $l3->created;
            }

            $fila_array = array(
                'user_id' => $fila->user_id,
                'user_name' => $fila->username,
                'first_name' => $fila->first_name,
                'last_name' => $fila->last_name,
                'company_id' => $fila->company_id,
                'email' => $fila->email,
                'cell_number' => $fila->cell_number,
                'phone_number' => $fila->phone_number,
                'profile_image' => $pic,
                'id' => $id,
                'lng' => $lng,
                'lat' => $lat,
                'lastdate' => $lastdate,
                'lastaddress' => $address

            );

            // Recorrer cada columna de la fila


            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function gpsuserLista_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
        }


        $consulta = "SELECT * FROM vendors ";

        if ($userData['user_id'] != null) {
            $consulta = $consulta . " Where user_id = " . $userData['user_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila

            $pic = base_url() . 'images/no_image_user.png';

            if ($fila->profile_image != "") {
                $pic = base_url() . 'uploads/users/' . $fila->profile_image;
            } else {
                $pic = base_url() . 'images/no_image_user.png';
            }

            $lng = 0.00;
            $lat = 0.00;
            $address = "";
            $lastdate = "";
            $id = 0;

            $consulta2 = "SELECT id,created, longitude, latitude, address FROM gpstracker WHERE user_id = " . $fila->user_id . " and login = 1 order by id desc limit 1";

            $list3 = $this->Apimodel->fetch_all_join($consulta2);

            foreach ($list3 as $l3) {
                $lng = $l3->longitude;
                $lat = $l3->latitude;
                $address = $l3->address;
                $id = $l3->id;
                $lastdate = $l3->created;
            }

            $fila_array = array(
                'user_id' => $fila->user_id,
                'user_name' => $fila->username,
                'first_name' => $fila->first_name,
                'last_name' => $fila->last_name,
                'company_id' => $fila->company_id,
                'email' => $fila->email,
                'cell_number' => $fila->cell_number,
                'phone_number' => $fila->phone_number,
                'profile_image' => $pic,
                'id' => $id,
                'lng' => $lng,
                'lat' => $lat,
                'lastdate' => $lastdate,
                'lastaddress' => $address

            );

            // Recorrer cada columna de la fila


            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }


    public function gpssavetrackList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['id'] = $this->post('id');
            $userData['address'] = $this->post('address');
        }

        $mydata = array(
            'address' => $userData['address']
        );

        $userId = $userData['id'];
        $where = "id=$userId";
        $update = $this->Apimodel->update_cond('gpstracker', $where, $mydata);

    }

    public function gpstrackList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['user_type'] = $this->post('user_type');
            $userData['fecha1'] = $this->post('fecha1');
            $userData['fecha2'] = $this->post('fecha2');
        }


        $consulta = "SELECT * FROM gpstracker ";

        $consulta1 = "SELECT * FROM vendors WHERE user_id=" . $userData['user_id'];

        if ($userData['user_id'] != null) {

            $consulta = $consulta . " Where user_id = " . $userData['user_id'] . " AND login = " . $userData['user_type'] . " AND DATE(created) >='" . $userData['fecha1'] . "' AND DATE(created) <= '" . $userData['fecha2'] . "'";

        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $user = $this->Apimodel->fetch_all_join($consulta1);

        $data1 = array();

        foreach ($user as $fila1) {
            // Array para almacenar los valores de esta fila

            $pic = base_url() . 'images/no_image_user.png';

            if ($fila1->profile_image != "") {
                $pic = base_url() . 'uploads/users/' . $fila1->profile_image;
            } else {
                $pic = base_url() . 'images/no_image_user.png';
            }


            $fila_array1 = array(
                'user_id' => $fila1->user_id,
                'user_name' => $fila1->username,
                'first_name' => $fila1->first_name,
                'last_name' => $fila1->last_name,
                'company_id' => $fila1->company_id,
                'email' => $fila1->email,
                'cell_number' => $fila1->cell_number,
                'phone_number' => $fila1->phone_number,
                'profile_image' => $pic,

            );

            $data1[] = $fila_array1;
        }

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila

            $fila_array = array(
                'id' => $fila->id,
                'user_id' => $fila->user_id,
                'company_id' => $fila->company_id,
                'longitude' => $fila->longitude,
                'latitude' => $fila->latitude,
                'address' => $fila->address,
                'created' => $fila->created,
                'login' => $fila->login

            );

            // Recorrer cada columna de la fila


            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);
        $data1 = $this->arrcheck($data1);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'userInfo' => $data1,
            'list' => $data,
            'consulta' => $consulta
        ], 200);


    }


    public function gpstrackLista_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['user_type'] = $this->post('user_type');
        }


        $consulta = "SELECT * FROM gpstracker ";

        $consulta1 = "SELECT * FROM vendors WHERE user_id=" . $userData['user_id'];

        if ($userData['user_id'] != null) {
            $consulta = $consulta . " Where user_id = " . $userData['user_id'] . " AND login = " . $userData['user_type'] . " ORDER BY created DESC";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $user = $this->Apimodel->fetch_all_join($consulta1);

        $data1 = array();

        foreach ($user as $fila1) {
            // Array para almacenar los valores de esta fila

            $pic = base_url() . 'images/no_image_user.png';

            if ($fila1->profile_image != "") {
                $pic = base_url() . 'uploads/users/' . $fila1->profile_image;
            } else {
                $pic = base_url() . 'images/no_image_user.png';
            }


            $fila_array1 = array(
                'user_id' => $fila1->user_id,
                'user_name' => $fila1->username,
                'first_name' => $fila1->first_name,
                'last_name' => $fila1->last_name,
                'company_id' => $fila1->company_id,
                'email' => $fila1->email,
                'cell_number' => $fila1->cell_number,
                'phone_number' => $fila1->phone_number,
                'profile_image' => $pic,

            );

            $data1[] = $fila_array1;
        }

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila

            $fila_array = array(
                'id' => $fila->id,
                'user_id' => $fila->user_id,
                'company_id' => $fila->company_id,
                'longitude' => $fila->longitude,
                'latitude' => $fila->latitude,
                'address' => $fila->address,
                'created' => $fila->created,
                'login' => $fila->login

            );

            // Recorrer cada columna de la fila


            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);
        $data1 = $this->arrcheck($data1);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'userInfo' => $data1,
            'list' => $data,
            'consulta' => $consulta
        ], 200);


    }

    public function warehouseusersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM warehouse_users ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function inventoryordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM inventory_orders ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function odetailsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT
            detail_id,
            order_id,
            product_id,
            sku,
            name,
            qty,
            delivered_quantity,
            delivered_pack,
            pack,
            discount,
            discount_type,
            sale_price,
            fob_price,
            purchase_price,
            comment,
            created,
            amount_sales,
            discount_amount,
            total_amount,
            amount_delivered,
            discount_delivered,
            total_delivered
            FROM odetaillist ";
        $consulta = $consulta . " Where company_id = " . $userData['company_id'];

        /*if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }*/


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        $this->response([
            'status' => "1",
            'message' => 'Listed',
            'list' => $data
        ], 200);


    }

    public function softodetailsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT
            detail_id,
            order_id,
            product_id
            sku,
            name,
            qty,
            delivered_quantity,
            delivered_pack,
            pack,
            discount,
            discount_type,
            sale_price,
            fob_price,
            purchase_price,
            comment,
            created,
            amount_sales,
            discount_amount,
            total_amount,
            amount_delivered,
            discount_delivered,
            total_delivered
            FROM odetaillist ";
        $consulta = $consulta . " Where company_id = " . $userData['company_id'];

        /*if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }*/


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);


        echo json_encode($data);


    }

    public function wodetailsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
        }


     	$consulta = "SELECT * FROM oDetailList ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'] . " AND created >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)  AND created <= CURDATE()";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);


        echo json_encode($data);


    }

    public function bakwodetailsList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM bakodetaillist ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);


        echo json_encode($data);


    }

    public function tbInventoryList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM tb_inventory ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function warehouseordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM warehouseorders ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function wwarehouseordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM warehouseorders ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'] . " AND ";
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function bakwarehouseordersList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $consulta = "SELECT * FROM bakwarehouseorders ";

        if ($userData['company_id'] != null) {
            $consulta = $consulta . " Where company_id = " . $userData['company_id'];
        }


        $list = $this->Apimodel->fetch_all_join($consulta);

        $data = array();

        // Recorrer cada fila de los resultados
        foreach ($list as $fila) {
            // Array para almacenar los valores de esta fila
            $fila_array = array();

            // Recorrer cada columna de la fila
            foreach ($fila as $nombre_campo => $valor) {
                // Añadir el valor al array asociativo usando el nombre del campo como clave
                $fila_array[$nombre_campo] = $valor;
            }

            // Añadir el array de esta fila al array de datos
            $data[] = $fila_array;
        }

        $data = $this->arrcheck($data);

        echo json_encode($data);


    }

    public function login_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['email'] = $this->post('email');
            $userData['password'] = $this->post('password');
            $userData['user_type'] = $this->post('user_type');
        }

        // user_type 1=vendor, 2=warehouse manager

        $this->form_validation->set_rules('email', 'email', 'trim|required');
        $this->form_validation->set_rules('password', 'password', 'trim|required');
        $this->form_validation->set_rules('user_type', 'user_type', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('email')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('email'))
                ], 400);
            }

            if (form_error('password')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('password'))
                ], 400);
            }

            if (form_error('user_type')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_type'))
                ], 400);
            }

        } else {

            $user_type = $userData['user_type'];

            if ($user_type == "1") {
                $where = "email = '" . $userData['email'] . "' OR username='" . $userData['email'] . "'";
                $userTable = "vendors";
            } else {
                $where = "email = '" . $userData['email'] . "'";
                $userTable = "warehouse_users";
            }

            if ($this->Commonmodel->count($userTable, $where) != 1) {
                $this->response([
                    'status' => "0",
                    'error' => "Invalid Email"
                ], 400);
            } else {

                $user = $this->Commonmodel->get_by($userTable, true, $where);
				$allcustomers = "1";
				$allcustomers1 = "";

				if($user_type=="1")
				{
					$allcustomers = $user->all_customers;
					$allcustomers1 = $user->all_customers;
				}

				if ($allcustomers != "1") {
					$allcustomers = "0";
				}

				if ($allcustomers1 != "1") {
					$allcustomers1 = "0";
				}

                if (password_verify($userData['password'], $user->password) == 0) {
                    $this->response([
                        'status' => "0",
                        'error' => "Invalid Password"
                    ], 400);
                } elseif ($user->status == '0') {
                    $this->response([
                        'status' => "0",
                        'error' => "Your account has not active. Please verify."
                    ], 400);

                } else {

                    if ($user->profile_image != "") {
                        $pic = base_url() . 'uploads/users/' . $user->profile_image;
                    } else {
                        $pic = base_url() . 'images/no_image_user.png';
                    }

                    $usrProfile = $this->Apimodel->get_cond('user_profiles', "profile_id=$user->profile");

                    $companyInfo = $this->Apimodel->get_cond('companies', "company_id=$user->company_id");

                    $countryInfo = $this->Apimodel->get_cond('countries', "id=$companyInfo->country");

                    $termInfo = $this->Apimodel->get_cond('terms_of_sales', "company_id=$user->company_id AND default<>0");

                    $groupInfo = $this->Apimodel->get_cond('customer_groups', "company_id=$user->company_id AND default<>0");


                    //$countryInfo = $this->Commonmodel->get_by('countries', true, "id=".$companyInfo->country."");
                    //$termInfo = $this->Commonmodel->get_by('term_of_sales', true, "id=".$companyInfo->country."");

                    if ($companyInfo->image != "") {
                        $pic1 = base_url() . 'uploads/company/' . $companyInfo->image;
                    } else {
                        $pic1 = base_url() . 'images/no_image_user.png';
                    }

                    $arraycountry = [
                        'id' => $countryInfo->id,
                        'name' => $countryInfo->name
                    ];

                    $arrayterm = [
                        'id' => $termInfo->term_id,
                        'name' => $termInfo->name
                    ];

                    $arraygroup = [
                        'id' => $groupInfo->group_id,
                        'name' => $groupInfo->name
                    ];

                    $arrayt = $this->arrcheck($arrayterm);
                    $arrayc = $this->arrcheck($arraygroup);
                    $arraycy = $this->arrcheck($arraycountry);

                    $mydata = array(
                        'user_id' => $user->user_id,
                        'company_id' => $user->company_id,
                        'log_type' => 'login',
                        'created_on' => date("Y-m-d H:i:s")
                    );

                    $this->Apimodel->add_details("logs", $mydata);




                    $array = [
                        'status' => "1",
                        'user_type' => $user_type,
                        'info' => [
                            'user_id' => $user->user_id,
                            'unique_id' => $user->unique_id,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'phone_number' => $user->phone_number,
                            'profile_image' => $pic,
                            'company_image' => $pic1,
                            'profile' => @$usrProfile->profile_name,
                            'company_id' => $user->company_id,
                            'company_name' => $companyInfo->name,
                            'show_inventory' => $companyInfo->show_inventory,
                            'language_id' => $companyInfo->language_id,
                            'order_discount' => @$usrProfile->order_discount,
                            'order_net_discount' => @$usrProfile->order_net_discount,
                            'can_change_price' => @$usrProfile->can_change_price,
                            'can_send_catalog' => @$usrProfile->can_send_catalog,
                            'can_create_customer' => @$usrProfile->can_create_customer,
                            'addcustomer' => @$user->add_customer,
                            'updatecustomer' => @$user->update_customer,
                            'sendcatalog' => @$user->send_catalog,
							'allcustomers' => @$allcustomers,
                            'term_sales' => $arrayt,
                            'customer_groups' => $arrayc,
                            'country_info' => $arraycy
                        ]
                    ];

                    $array = $this->arrcheck($array);

                    $this->response($array, 200);

                }
            }
        }
    }

    public function updateProfile_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['first_name'] = $this->post('first_name');
            $userData['last_name'] = $this->post('last_name');
            $userData['phone_number'] = $this->post('phone_number');
            $userData['profile_image'] = $this->post('profile_image');
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
        $this->form_validation->set_rules('first_name', 'first_name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'last_name', 'trim|required');
        $this->form_validation->set_rules('phone_number', 'phone_number', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], 400);
            }

            if (form_error('first_name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('first_name'))
                ], 400);
            }

            if (form_error('last_name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('last_name'))
                ], 400);
            }
            if (form_error('phone_number')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('phone_number'))
                ], 400);
            }

        } else {

            $userId = $userData['user_id'];
            $dataraw = $this->Apimodel->get_cond('vendors', "user_id=$userId");

            if (!empty($dataraw)) {
                $config['upload_path'] = './uploads/users/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size'] = 204800;
                $config['file_name'] = uniqid();
                $this->load->library('upload', $config);

                if (!$this->upload->do_upload('profile_image')) {
                    $error = array('error' => $this->upload->display_errors());
                    @$image = $dataraw->profile_image;

                } else {
                    $file_data = $this->upload->data();
                    $image = $file_data['file_name'];

                }

                $mydata = array(
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'profile_image' => $image,
                    'phone_number' => $userData['phone_number']
                );

                $where = "user_id=$userId";
                $update = $this->Apimodel->update_cond('vendors', $where, $mydata);

                $user = $this->Apimodel->get_cond('vendors', "user_id=$userId");

                if ($user->profile_image != "") {
                    $pic = base_url() . 'uploads/users/' . $user->profile_image;
                } else {
                    $pic = base_url() . 'images/no_image_user.png';
                }

                $usrProfile = $this->Apimodel->get_cond('user_profiles', "profile_id=$user->profile");

                $arr = array(
                    'user_id' => $user->user_id,
                    'unique_id' => $user->unique_id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'profile_image' => $pic,
                    'profile' => $usrProfile->profile_name,
                    'order_discount' => $usrProfile->order_discount,
                    'order_net_discount' => $usrProfile->order_net_discount,
                    'can_change_price' => $usrProfile->can_change_price,
                    'can_send_catalog' => $usrProfile->can_send_catalog,
                    'can_create_customer' => $usrProfile->can_create_customer
                );
                $arr = $this->arrcheck($arr);

                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Profile updated successfully.',
                        'info' => $arr
                    ], 200);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller:: HTTP_NOT_FOUND);
                }

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No user found.'
                ], REST_Controller::HTTP_NOT_FOUND);

            }

        }
    }

    public function userInfo_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
        }
        $this->form_validation->set_rules('user_id', 'user_id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], 400);
            }
        } else {

            $id = $userData['user_id'];
            $user = $this->Apimodel->get_cond('vendors', "user_id=$id");
            if (!empty($user)) {
                if ($user->profile_image != "") {
                    $pic = base_url() . 'uploads/users/' . $user->profile_image;
                } else {
                    $pic = base_url() . 'images/no_image_user.png';
                }

                $company = $this->Apimodel->get_cond('companies', "company_id=$user->company_id");

                $usrProfile = $this->Apimodel->get_cond('user_profiles', "profile_id=$user->profile");

                $array = [
                    'status' => "1",
                    'info' => [
                        'user_id' => $user->user_id,
                        'company_id' => $user->company_id,
                        'company_name' => @$company->name,
                        'unique_id' => $user->unique_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone_number' => $user->phone_number,
                        'profile_image' => $pic,
                        'profile' => $usrProfile->profile_name,
                        'order_discount' => $usrProfile->order_discount,
                        'order_net_discount' => $usrProfile->order_net_discount,
                        'can_change_price' => $usrProfile->can_change_price,
                        'can_send_catalog' => $usrProfile->can_send_catalog,
                        'can_create_customer' => $usrProfile->can_create_customer
                    ]
                ];

                $array = $this->arrcheck($array);
                $this->response($array, 200);

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function changePassword_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;

        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['old_password'] = $this->post('old_password');
            $userData['new_password'] = $this->post('new_password');
        }

        $this->form_validation->set_rules('user_id', 'user_id', 'trim|required');
        $this->form_validation->set_rules('old_password', 'old_password', 'trim|required');
        $this->form_validation->set_rules('new_password', 'new_password', 'trim|required|min_length[6]');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], 400);
            }

            if (form_error('old_password')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('old_password'))
                ], 400);
            }

            if (form_error('new_password')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('new_password'))
                ], 400);
            }

        } else {

            $encrptpass = $this->enc_password($userData['old_password']);
            $userId = $userData['user_id'];
            $where = "user_id = '$userId'";
            $details = $this->Apimodel->get_cond('vendors', $where);

            if ($details) {
                if (password_verify($userData['old_password'], $details->password) == 0) {
                    $this->response([
                        'status' => "0",
                        'user_id' => $userId,
                        'message' => 'Old password is not matched!'
                    ], 200);

                }

                $data = array(
                    'password' => $this->enc_password($userData['new_password'])
                );

                $where = "user_id = $userId";
                $update = $this->Apimodel->update_cond('vendors', $where, $data);
                if ($update) {

                    $this->response([
                        'status' => "1",
                        'user_id' => $userId,
                        'message' => 'Password updated successfully.'
                    ], 200);

                } else {
                    $this->response([
                        'status' => "0",
                        'user_id' => $userId,
                        'message' => 'Password updated successfully.'
                    ], 200);
                }
            } else {

                $this->response([
                    'status' => "0",
                    'user_id' => $userId,
                    'message' => 'Error Ocurred.'
                ], 200);

            }

        }
    }

    public function languages_get()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_GET = (array)$obj;
            $userData = $_GET;
        }

        $list = $this->Apimodel->get_cond_all('languages', "status='1'");

        if (!empty($list)) {
            foreach ($list as $lang) {

                $array[] = [
                    'language_id' => $lang->language_id,
                    'name' => @$lang->name,
                    'iso_639_1' => @$lang->iso_639_1,
                    'status' => @$lang->status,
                ];
            }

            $array = $this->arrcheck($array);

            $this->response([
                'status' => "1",
                'languages' => $array
            ], 200);
        } else {
            $this->response([
                'status' => "0",
                'error' => 'No Data found.'
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function categoryList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }
            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];

            //$getCategorySql = "SELECT * FROM `categoryList` WHERE  company_id ='".$company_id."'";
            $getCategorySql = "SELECT
			A.`order` AS order_id,
			B.*,
			ifnull(D.items,0) AS items
			FROM order_categories A
			LEFT JOIN categories B ON B.cat_id = A.category_id
			INNER JOIN catalog C ON C.catalog_id = A.catalog_id
			LEFT JOIN category_count D ON D.category_value = A.category_id
			WHERE C.catalog_name = 'FullVendor Catalog'  AND B.category_status = '1' AND ifnull(D.items,0)>0  AND A.company_id = '" . $company_id . "'
			ORDER BY A.company_id,  A.`order`";

            $list = $this->Apimodel->fetch_all_join($getCategorySql);

            if (!empty($list)) {
                foreach ($list as $cat) {
                    if ($cat->images != "") {
                        $pic = base_url() . 'uploads/categories/' . $company_id . '/' . $cat->images;
                    } else {
                        $pic = base_url() . 'images/noimg.png';
                    }

                    $array[] = [
                        'order_id' => $cat->order_id,
                        'category_id' => $cat->cat_id,
                        'company_id' => $cat->company_id,
                        'cat_id' => $cat->cat_id,
                        'language_id' => "1",
                        'category_status' => $cat->category_status,
                        'category_created_at' => $cat->category_created_at,
                        'id_kor' => $cat->id_kor,
                        'category_name' => @$cat->category_name,
                        'images' => @$pic
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function productList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];

            $companyInfo = $this->Apimodel->get_cond('companies', "company_id=$company_id");

            $catalog_id = 0;

            $catlist = $this->Apimodel->get_cond_all('catalog', "catalog_name='FullVendor Catalog' AND `company_id`='" . $company_id . "' LIMIT 1");

            if (!empty($catlist)) {
                foreach ($catlist as $pc) {
                    $catalog_id = $pc->catalog_id;
                }
            }

            $type = $userData['category_id'];
            $cat = $userData['category_id'];


            if (!empty($cat)) {


                /*$fullvendorconsulta1 = "SELECT '1' as consulta, od.order, p.* FROM order_categories od
			INNER JOIN products p ON FIND_IN_SET(od.category_id,p.category_id)
			INNER JOIN categories c ON c.category_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id
			AND p.status = 1
			AND c.category_status = 1
			AND c.category_id = $cat
			AND p.name is not NULL
			and p.sale_price > 0
			GROUP BY p.pro_id
			ORDER BY  od.order ASC,p.name ASC";*/

                $fullvendorconsulta1 = "SELECT od.order, p.* FROM order_categories od
			INNER JOIN products p ON FIND_IN_SET(od.category_id,p.category_id)
			WHERE p.status = 1 AND od.catalog_id = $catalog_id AND od.company_id =$company_id AND p.category_id LIKE '%$cat%'
			ORDER BY od.order, p.name ASC;";


                //$fullvendorconsulta1 = "SELECT  p.* FROM products p WHERE p.company_id = $company_id AND p.category_id LIKE '%$cat%' AND p.status = 1 AND p.name is not NULL  AND p.sale_price > 0 	ORDER BY  p.name ASC;";


                $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);


            } else {


                /*$fullvendorconsulta1 = "SELECT 2 as consulta,od.order, p.* FROM order_categories od
		  INNER JOIN products p ON FIND_IN_SET(od.category_id,p.category_id)
		  INNER JOIN categories c ON c.category_id = od.category_id
		  WHERE od.company_id = $company_id
		  AND od.catalog_id = $catalog_id
		  AND p.status = 1
		  AND c.category_status = 1
		  AND p.name is not NULL
		  and p.sale_price > 0
		  GROUP BY p.pro_id
		  ORDER BY  od.order ASC,p.name ASC";*/


                //$fullvendorconsulta1 = "SELECT  p.* FROM products p WHERE p.company_id = $company_id  AND p.status = 1  AND p.name is not NULL  and p.sale_price > 0  ORDER BY  p.name ASC;";


                $fullvendorconsulta1 = "SELECT od.order, p.* FROM order_categories od
			INNER JOIN products p ON FIND_IN_SET(od.category_id,p.category_id)
			WHERE  p.status = 1 AND od.catalog_id = $catalog_id AND od.company_id =$company_id
			ORDER BY od.order, p.name ASC;";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}	*/

                //$fullvendorconsulta1 .="od.order, p.name ASC;";


                if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', "status='1' AND  `company_id`='" . $company_id . "'  AND sale_price>0");
                } else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }


            }




            if (!empty($list)) {
                $orden = 0;
                $ii = 0;
                foreach ($list as $p) {

                    $pedidosList = array();

                    $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$p->product_id AND customer_id=$customer_id");


                    foreach ($pedidos as $ped) {
                        $pedidosList[] = array(
                            'customer_id' => $ped->customer_id,
                            'qty' => $ped->qty,
                            'requested' => $ped->requested,
                        );
                    }


                    $gallleryList = array();

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$p->product_id ");

                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'product_id' => $p->pro_id,
                            'company_id' => $p->company_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                        );
                    }

                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = ($p->stock - $total_quantity);

                    $lblstock = "";

                    if ($companyInfo->show_inventory == 1) {
                        $lblstock = "Stock: $p->stock";
                    }

                    if ($p->name != null || $p->name !="") {
                    $array[] = [
                        'tipo' => $cat,
                        'consulta' => '1',
                        'catalog_order' => $orden,
                        'FilaOrden' => $orden,
                        'catalog_id' => $catalog_id,
                        'product_id' => $p->product_id,
                        'name' => strtoupper($p->name),
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'lblstock' => $lblstock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'force_moq' => $p->notify_minimum_stock,
                        'status' => $p->status,
                        'currency_type' => $this->getSymbol($p->currency_type),
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ];
                    $orden++;
                    $ii++;
                }
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array,

                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found. '
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function wproductList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];


            $companyInfo = $this->Apimodel->get_cond('companies', "company_id=$company_id");

            $catalog_id = 0;

            $catlist = $this->Apimodel->get_cond_all('catalog', "catalog_name='FullVendor Catalog' AND `company_id`='" . $company_id . "' LIMIT 1");

            if (!empty($catlist)) {
                foreach ($catlist as $pc) {
                    $catalog_id = $pc->catalog_id;
                }
            }

            $type = $userData['category_id'];


            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);


                $fullvendorconsulta1 = "SELECT od.order,p.*,pi.images,img.cImg,imp.imploded AS implode_img
		  FROM order_categories AS od
		  INNER JOIN catalog AS catl ON catl.catalog_id = od.catalog_id
		  INNER JOIN categories AS c ON c.cat_id = od.category_id
		  INNER JOIN products AS p ON FIND_IN_SET(od.category_id,p.category_id)
		  LEFT JOIN images_count AS img ON p.pro_id  = img.product_id
		  LEFT JOIN implode_img as imp ON imp.product_id=p.pro_id
		  LEFT JOIN product_images AS pi ON p.pro_id  = pi.product_id
		  WHERE od.company_id = $company_id AND od.catalog_id = $catalog_id
		  AND p.category_id REGEXP ('$category_ids')		  
		  AND c.category_status = 1
		  AND p.name is not NULL
		  and p.sale_price > 0
		  GROUP BY p.pro_id
		  ORDER BY ";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}*/
                $fullvendorconsulta1 .= "od.order, p.name ASC;";

				
            
                if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', " category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");
                } else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }


                if (!empty($list)) {
                    $list = $this->Apimodel->get_cond_all('products', " category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");
                }


            } else {

                $fullvendorconsulta1 = "SELECT od.order,p.*,pi.images,img.cImg,imp.imploded AS implode_img
			FROM order_categories AS od
			INNER JOIN catalog AS catl ON catl.catalog_id = od.catalog_id
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			INNER JOIN products AS p ON FIND_IN_SET(od.category_id,p.category_id)
			LEFT JOIN images_count AS img ON p.pro_id  = img.product_id
			LEFT JOIN implode_img as imp ON imp.product_id=p.pro_id
			LEFT JOIN product_images AS pi ON p.pro_id  = pi.product_id
			WHERE od.company_id = $company_id AND od.catalog_id = $catalog_id			
			AND c.category_status = 1
			AND p.name is not NULL
			and p.sale_price > 0
			GROUP BY p.pro_id
			ORDER BY  ";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}	*/
                $fullvendorconsulta1 .= "od.order, p.name ASC;";

				
            
                if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', "  `company_id`='" . $company_id . "'  AND sale_price>0");
                } else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }


            }


            $orden = 0;

            if (!empty($list)) {
                foreach ($list as $p) {


                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = "0"; //$this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = 0; //($p->stock - $total_quantity);

                    $lblstock = "";

                    if ($companyInfo->show_inventory == 1) {
                        $lblstock = "Stock: $p->stock";
                    }

                    $array[] = [
                        'FilaOrden' => $orden,
                        'fila' => $orden,
                        'catalog_order' => $orden,
                        'catalog_id' => $catalog_id,
                        'product_id' => $p->product_id,
                        'name' => strtoupper($p->name),
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'lblstock' => $lblstock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'force_moq' => $p->notify_minimum_stock,
                        'status' => $p->status,
                        'currency_type' => $this->getSymbol($p->currency_type)
                    ];
                    $orden++;
                }

                $array = $this->arrcheck($array);

                echo json_encode($array);

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found. '
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function wwproductList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];


            $companyInfo = $this->Apimodel->get_cond('companies', "company_id=$company_id");

            

            $catlist = $this->Apimodel->get_cond_all('catalog', "catalog_name='FullVendor Catalog' AND `company_id`='" . $company_id . "' LIMIT 1");

            if (!empty($catlist)) {
                foreach ($catlist as $pc) {
                    $catalog_id = $pc->catalog_id;
                }
            }

            $type = $userData['category_id'];


            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);


                $fullvendorconsulta1 = "SELECT od.order,p.*,pi.images,img.cImg,imp.imploded AS implode_img
		  FROM order_categories AS od
		  INNER JOIN catalog AS catl ON catl.catalog_id = od.catalog_id
		  INNER JOIN categories AS c ON c.cat_id = od.category_id
		  INNER JOIN products AS p ON FIND_IN_SET(od.category_id,p.category_id)
		  LEFT JOIN images_count AS img ON p.pro_id  = img.product_id
		  LEFT JOIN implode_img as imp ON imp.product_id=p.pro_id
		  LEFT JOIN product_images AS pi ON p.pro_id  = pi.product_id
		  WHERE od.company_id = $company_id AND od.catalog_id = $catalog_id 
		  AND p.category_id REGEXP ('$category_ids')
          AND p.status = 1
		  AND c.category_status = 1
		  AND p.name is not NULL
		  and p.sale_price > 0
		  GROUP BY p.pro_id
		  ORDER BY ";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}*/
                $fullvendorconsulta1 .= "od.order, p.name ASC;";

 				
            
                if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', " category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");
                } else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }


                if (!empty($list)) {
                    $list = $this->Apimodel->get_cond_all('products', " category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");
                }


            } else {

                $fullvendorconsulta1 = "SELECT od.order,p.*,pi.images,img.cImg,imp.imploded AS implode_img
			FROM order_categories AS od
			INNER JOIN catalog AS catl ON catl.catalog_id = od.catalog_id
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			INNER JOIN products AS p ON FIND_IN_SET(od.category_id,p.category_id)
			LEFT JOIN images_count AS img ON p.pro_id  = img.product_id
			LEFT JOIN implode_img as imp ON imp.product_id=p.pro_id
			LEFT JOIN product_images AS pi ON p.pro_id  = pi.product_id
			WHERE od.company_id = $company_id AND od.catalog_id = $catalog_id  
            AND p.status = 1
			AND c.category_status = 1
			AND p.name is not NULL
			and p.sale_price > 0
			GROUP BY p.pro_id
			ORDER BY  ";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}	*/
                $fullvendorconsulta1 .= "od.order, p.name ASC;";

 				
            
                if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', " `company_id`='" . $company_id . "'  AND sale_price>0");
                } else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }


            }


            $orden = 0;

            if (!empty($list)) {
                foreach ($list as $p) {


                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = "0"; //$this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = 0; //($p->stock - $total_quantity);

                    $lblstock = "";

                    if ($companyInfo->show_inventory == 1) {
                        $lblstock = "Stock: $p->stock";
                    }

                    $array[] = [
                        'FilaOrden' => $orden,
                        'fila' => $orden,
                        'catalog_order' => $orden,
                        'catalog_id' => $catalog_id,
                        'product_id' => $p->product_id,
                        'name' => strtoupper($p->name),
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'lblstock' => $lblstock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'force_moq' => $p->notify_minimum_stock,
                        'status' => $p->status,
                        'currency_type' => $this->getSymbol($p->currency_type)
                    ];
                    $orden++;
                }

                $array = $this->arrcheck($array);

                echo json_encode($array);

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found. '
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function wsproductList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];


            $companyInfo = $this->Apimodel->get_cond('companies', "company_id=$company_id");

            $catalog_id = 0;

            $catlist = $this->Apimodel->get_cond_all('catalog', "catalog_name='FullVendor Catalog' AND `company_id`='" . $company_id . "' LIMIT 1");

            if (!empty($catlist)) {
                foreach ($catlist as $pc) {
                    $catalog_id = $pc->catalog_id;
                }
            }

            $type = $userData['category_id'];


            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);


                $fullvendorconsulta1 = "SELECT od.order,p.*,pi.images,img.cImg,imp.imploded AS implode_img
		  FROM order_categories AS od
		  INNER JOIN catalog AS catl ON catl.catalog_id = od.catalog_id
		  INNER JOIN categories AS c ON c.cat_id = od.category_id
		  INNER JOIN products AS p ON FIND_IN_SET(od.category_id,p.category_id)
		  LEFT JOIN images_count AS img ON p.pro_id  = img.product_id
		  LEFT JOIN implode_img as imp ON imp.product_id=p.pro_id
		  LEFT JOIN product_images AS pi ON p.pro_id  = pi.product_id
		  WHERE od.company_id = $company_id AND od.catalog_id = $catalog_id 
		  AND p.category_id REGEXP ('$category_ids')
		  AND p.`status` = 1
		  AND c.category_status = 1
		  AND p.name is not NULL
		  and p.sale_price > 0
		  GROUP BY p.pro_id
		  ORDER BY ";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}*/
                $fullvendorconsulta1 .= "od.order, p.name ASC;";

 				$catalog_id = 0;
            
                //if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', " category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");
                /*} else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }*/


                if (!empty($list)) {
                    $list = $this->Apimodel->get_cond_all('products', " category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");
                }


            } else {

                $fullvendorconsulta1 = "SELECT od.order,p.*,pi.images,img.cImg,imp.imploded AS implode_img
			FROM order_categories AS od
			INNER JOIN catalog AS catl ON catl.catalog_id = od.catalog_id
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			INNER JOIN products AS p ON FIND_IN_SET(od.category_id,p.category_id)
			LEFT JOIN images_count AS img ON p.pro_id  = img.product_id
			LEFT JOIN implode_img as imp ON imp.product_id=p.pro_id
			LEFT JOIN product_images AS pi ON p.pro_id  = pi.product_id
			WHERE od.company_id = $company_id AND od.catalog_id = $catalog_id  
			AND p.`status` = 1
			AND c.category_status = 1
			AND p.name is not NULL
			and p.sale_price > 0
			GROUP BY p.pro_id
			ORDER BY  ";

                $query = "SELECT od.order,c.cat_id FROM order_categories as od
			INNER JOIN categories AS c ON c.cat_id = od.category_id
			WHERE od.company_id = $company_id
			AND od.catalog_id = $catalog_id";

                $listacat = $this->Apimodel->fetch_all_join($query);

                /*foreach ($listacat as $cat){
				$id = $cat->cat_id;
				$fullvendorconsulta1 .=" FIND_IN_SET($id,p.category_id) DESC,";
			}	*/
                $fullvendorconsulta1 .= "od.order, p.name ASC;";

 				$catalog_id = 0;
            
               // if ($catalog_id == 0) {
                    $list = $this->Apimodel->get_cond_all('products', " `company_id`='" . $company_id . "'  AND sale_price>0");
                /*} else {
                    $list = $this->Apimodel->fetch_all_join($fullvendorconsulta1);
                }*/


            }


            $orden = 0;

            if (!empty($list)) {
                foreach ($list as $p) {


                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = "0"; //$this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = 0; //($p->stock - $total_quantity);

                    $lblstock = "";

                    if ($companyInfo->show_inventory == 1) {
                        $lblstock = "Stock: $p->stock";
                    }

                    $array[] = [
                        'FilaOrden' => $orden,
                        'fila' => $orden,
                        'catalog_order' => $orden,
                        'catalog_id' => $catalog_id,
                        'product_id' => $p->product_id,
                        'name' => strtoupper($p->name),
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'lblstock' => $lblstock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'force_moq' => $p->notify_minimum_stock,
                        'status' => $p->status,
                        'currency_type' => $this->getSymbol($p->currency_type)
                    ];
                    $orden++;
                }

                $array = $this->arrcheck($array);

                echo json_encode($array);

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found. '
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function productcheck_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];

            $type = $userData['category_id'];

            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);

                $list = $this->Apimodel->get_cond_all('products', "status='1' AND category_id REGEXP ('$category_ids') AND `language_id`='" . $language_id . "' AND `company_id`='" . $company_id . "'");

            } else {
                $list = $this->Apimodel->get_cond_all('products', "status='1' AND `language_id`='" . $language_id . "' AND `company_id`='" . $company_id . "'");
            }


            if (!empty($list)) {
                foreach ($list as $p) {
                    $gallleryList = array();

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$p->product_id ");

                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                        );
                    }

                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = ($p->stock - $total_quantity);

                    $array[] = [
                        'product_id' => $p->product_id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'currency_type' => $this->getSymbol($p->currency_type),
                        'images' => $gallleryList
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array,

                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }

    }


    public function getproductImages_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];

            $type = $userData['category_id'];


            $gallleryList = array();

            $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$language_id ");

            if (!empty($imgList)) {

                foreach ($imgList as $img) {
                    if ($img->images != "") {
                        $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                    } else {
                        $product_pic = base_url() . 'images/noimg.png';
                    }

                    $imageData = file_get_contents($product_pic);

                    // Crear una imagen a partir del contenido
                    $image = imagecreatefromstring($imageData);

                    // Comprimir la imagen (ajusta la calidad según sea necesario)
                    $compressedImageData = null;
                    imagejpeg($image, null, 30); // 50 es el factor de compresión (0-100)

                    // Convertir la imagen comprimida a un array de bytes
                    ob_start();
                    imagejpeg($image);
                    $compressedImageData = ob_get_contents();
                    ob_end_clean();

                    // Codificar el contenido de la imagen comprimida en base64
                    $imageBase64 = base64_decode($compressedImageData);


                    $gallleryList[] = array(
                        'product_id' => $p->pro_id,
                        'company_id' => $p->company_id,
                        'img_id' => $img->img_id,
                        'pic' => $product_pic,
                        'local' => $img->img_id . ".jpg",
                        'imageBlob' => $imageBase64,
                    );
                }


                $array[] = [
                    'images' => $gallleryList
                ];


                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array,

                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function getUpdate_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }


        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');


        if ($this->form_validation->run() === false) {


            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }

        } else {

            $company_id = $userData['company_id'];
            $product_pic = base_url() . 'uploads/gallery_' . $company_id . '.db';

            $imageData = file_get_contents($product_pic);


            $array[] = [
                'update' => date('Y-m-d'),
                'filename' => $product_pic,
                'filecontents' => base64_encode($imageData)
            ];


            $array = $this->arrcheck($array);

            $this->response([
                'status' => "1",
                'list' => $array,
            ], 200);
        }


    }


    public function getDatabaseInfo_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');


        if ($this->form_validation->run() === false) {


            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }

        } else {

            $company_id = $userData['company_id'];

            $list = $this->Apimodel->get_cond_all('versiones', "`company_id`='" . $company_id . "'");

            $array = array();

            foreach ($list as $p) {

                $array[] = [
                    'update' => $p->fecha,
                    'company_id' => $p->company_id,
                    'version' => $p->version
                ];

            }

            $array = $this->arrcheck($array);

            $this->response([
                'status' => "1",
                'list' => $array,
            ], 200);
        }


    }


    public function UpdateVersion_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');


        if ($this->form_validation->run() === false) {


            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }

        } else {

            $company_id = $userData['company_id'];

            $list = $this->Apimodel->get_cond_all('versiones', "`company_id`='" . $company_id . "'");

            $array = array();

            foreach ($list as $p) {

                $array[] = [
                    'update' => $p->fecha,
                    'company_id' => $p->company_id,
                    'version' => $p->version
                ];

            }

            $array = $this->arrcheck($array);

            $this->response([
                'status' => "1",
                'list' => $array,
            ], 200);
        }


    }


    public function createproductList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];

            $type = $userData['category_id'];

            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);

                $list = $this->Apimodel->get_cond_all('products', "status='1' AND category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");

            } else {
                $list = $this->Apimodel->get_cond_all('products', "status='1' AND  `company_id`='" . $company_id . "'  AND sale_price>0");
            }


            if (!empty($list)) {
                foreach ($list as $p) {

                    $pedidosList = array();

                    $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$p->product_id AND customer_id=$customer_id");


                    foreach ($pedidos as $ped) {
                        $pedidosList[] = array(
                            'customer_id' => $ped->customer_id,
                            'qty' => $ped->qty,
                            'requested' => $ped->requested,
                        );
                    }


                    $gallleryList = array();

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$p->product_id ");

                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }

                        $imageData = file_get_contents($product_pic);

                        // Crear una imagen a partir del contenido
                        $image = imagecreatefromstring($imageData);

                        // Comprimir la imagen (ajusta la calidad según sea necesario)
                        $compressedImageData = null;
                        imagejpeg($image, null, 30); // 50 es el factor de compresión (0-100)

                        // Convertir la imagen comprimida a un array de bytes
                        ob_start();
                        imagejpeg($image);
                        $compressedImageData = ob_get_contents();
                        ob_end_clean();

                        // Codificar el contenido de la imagen comprimida en base64
                        $imageBase64 = base64_decode($compressedImageData);


                        $where = array(
                            'image_id' => $img->image_id,
                        );

                        $mydata = array(
                            'imageBlob' => $imageBase64

                        );

                        $myupdate = $this->Apimodel->update_cond('product_images', $mydata, $where);


                        $gallleryList[] = array(
                            'product_id' => $p->pro_id,
                            'company_id' => $p->company_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                        );
                    }

                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = ($p->stock - $total_quantity);

                    $array[] = [
                        'product_id' => $p->product_id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'force_moq' => $p->notify_minimum_stock,
                        'currency_type' => $this->getSymbol($p->currency_type),
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array,

                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function syncproductList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $customer_id = $userData['customer_id'];

            $type = $userData['category_id'];

            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);

                $list = $this->Apimodel->get_cond_all('products', "status='1' AND category_id REGEXP ('$category_ids') AND `company_id`='" . $company_id . "' AND sale_price>0");

            } else {
                $list = $this->Apimodel->get_cond_all('products', "status='1' AND  `company_id`='" . $company_id . "'  AND sale_price>0");
            }


            if (!empty($list)) {
                foreach ($list as $p) {

                    $pedidosList = array();

                    $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$p->product_id AND customer_id=$customer_id");


                    foreach ($pedidos as $ped) {
                        $pedidosList[] = array(
                            'customer_id' => $ped->customer_id,
                            'qty' => $ped->qty,
                            'requested' => $ped->requested,
                        );
                    }


                    $gallleryList = array();

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$p->product_id ");

                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'product_id' => $p->pro_id,
                            'company_id' => $p->company_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                            'imageBlob' => $img->imageBlob,
                        );
                    }

                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";

                    $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);


                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = ($p->stock - $total_quantity);

                    $array[] = [
                        'product_id' => $p->product_id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'force_moq' => $p->notify_minimum_stock,
                        'currency_type' => $this->getSymbol($p->currency_type),
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array,

                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function inventoryList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['category_id'] = $this->post('category_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {

            $language_id = $userData['language_id'];

            $company_id = $userData['company_id'];

            $type = $userData['category_id'];

            if ($type) {
                $category_id = explode(",", $userData['category_id']);

                $category_ids = implode("|", $category_id);

                $list = $this->Apimodel->get_cond_all('products', "status='1' AND category_id REGEXP ('$category_ids')  AND `company_id`='" . $company_id . "'");

            } else {
                $list = $this->Apimodel->get_cond_all('products', "status='1'  AND `company_id`='" . $company_id . "'");
            }

            if (!empty($list)) {
                foreach ($list as $p) {
                    $gallleryList = array();

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$p->product_id");

                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'product_id' => $p->pro_id,
                            'company_id' => $p->company_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                        );
                    }

                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $p->product_id . "' AND od.company_id='" . $company_id . "'";
                    $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);

                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = ($p->stock - $total_quantity);

                    $array[] = [
                        'product_id' => $p->product_id,
                        'name' => $p->name,
                        'sku' => $p->sku,
                        'category_id' => $p->category_id,
                        'sale_price' => $p->sale_price,
                        'sale_price0' => $p->sale_price,
                        'fob_price' => $p->fob_price,
                        'purchase_price' => $p->purchase_price,
                        'barcode' => $p->barcode,
                        'tags' => $p->tags,
                        'descriptions' => $p->descriptions,
                        'unit_type' => $p->unit_type,
                        'stock' => $p->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'minimum_stock' => $p->minimum_stock,
                        'currency_type' => $this->getSymbol($p->currency_type),
                        'images' => $gallleryList
                    ];
                }

                $array = $this->arrcheck($array);
                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array,

                ], 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function cartImagesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {
            $company_id = $userData['company_id'];
            $gallleryList = array();
            $imgList = $this->Apimodel->get_cond_all('cartImages', "company_id=$company_id");

            if (!empty($imgList)) {
                foreach ($imgList as $img) {
                    if ($img->images != "") {
                        $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                    } else {
                        $product_pic = base_url() . 'images/noimg.png';
                    }

                    $gallleryList[] = [
                        'product_id' => $img->pro_id,
                        'company_id' => $img->company_id,
                        'img_id' => $img->img_id,
                        'pic' => $product_pic,
                        'local' => $img->img_id . ".jpg",
                    ];
                }


                $array = [
                    'status' => "1",
                    'details' => $gallleryList,
                ];

                $array = $this->arrcheck($array);
                $this->response($array, 200);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);

            }


        }


    }

    public function productDetails_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['product_id'] = $this->post('product_id');
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('product_id', 'product_id', 'trim|required');
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('product_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {
            $language_id = $userData['language_id'];
            $id = $userData['product_id'];
            $company_id = $userData['company_id'];

            $product = $this->Apimodel->get_cond('products', "product_id='$id'");

            $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE odt.product_id='" . $id . "' AND od.company_id='" . $company_id . "'";
            $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);

            if (!empty($totalOrder)) {
                if ($totalOrder->total_quantity) {
                    $total_quantity = @$totalOrder->total_quantity;
                } else {
                    $total_quantity = "0";
                }
            } else {
                $total_quantity = "0";
            }

            $availableStock = ($product->stock - $total_quantity);

            if (!empty($product)) {
                $gallleryList = array();

                $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$product->product_id ");


                $pedidosList = array();

                $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$product->product_id ");


                foreach ($pedidos as $ped) {
                    $pedidosList[] = array(
                        'customer_id' => $ped->customer_id,
                        'qty' => $ped->qty,
                        'requested' => $ped->requested,
                    );
                }


                foreach ($imgList as $img) {
                    if ($img->images != "") {
                        $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                    } else {
                        $product_pic = base_url() . 'images/noimg.png';
                    }

                    $gallleryList[] = [
                        'product_id' => $product->pro_id,
                        'company_id' => $product->company_id,
                        'img_id' => $img->img_id,
                        'pic' => $product_pic,
                        'local' => $img->img_id . ".jpg",
                    ];
                }

                $array = [
                    'status' => "1",
                    'details' => [
                        'product_id' => $product->product_id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'category_id' => $product->category_id,
                        'barcode' => $product->barcode,
                        'tags' => $product->tags,
                        'descriptions' => $product->descriptions,
                        'unit_type' => $product->unit_type,
                        'stock' => $product->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'currency_type' => $this->getSymbol($product->currency_type),
                        'minimum_stock' => $product->minimum_stock,
                        'force_moq' => $product->notify_minimum_stock,
                        'sale_price' => $product->sale_price,
                        'fob_price' => $product->fob_price,
                        'purchase_price' => $product->purchase_price,
                        'created_on' => $product->created_on,
                        'modified_on' => $product->modified_on,
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ]
                ];

                $array = $this->arrcheck($array);
                $this->response($array, 200);

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }

        }
    }

    public function inventoryDetails_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['product_id'] = $this->post('product_id');
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('product_id', 'product_id', 'trim|required');
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], 400);
            }

            if (form_error('product_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }
        } else {
            $language_id = $userData['language_id'];
            $id = $userData['product_id'];
            $company_id = $userData['company_id'];

            $product = $this->Apimodel->get_cond('products', "`company_id`='" . $company_id . "'");

            if (!empty($product)) {
                $gallleryList = array();

                $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$product->product_id");

                $pedidosList = array();

                $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$product->product_id ");


                foreach ($pedidos as $ped) {
                    $pedidosList[] = array(
                        'customer_id' => $ped->customer_id,
                        'qty' => $ped->qty,
                        'requested' => $ped->requested,
                    );
                }


                foreach ($imgList as $img) {
                    if ($img->images != "") {
                        $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                    } else {
                        $product_pic = base_url() . 'images/noimg.png';
                    }

                    $gallleryList[] = [
                        'product_id' => $product->pro_id,
                        'company_id' => $product->company_id,
                        'img_id' => $img->img_id,
                        'pic' => $product_pic,
                        'local' => $img->img_id . ".jpg",
                    ];
                }

                $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $product->product_id . "' AND od.company_id='" . $company_id . "'";
                $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);

                if (!empty($totalOrder)) {
                    if ($totalOrder->total_quantity) {
                        $total_quantity = @$totalOrder->total_quantity;
                    } else {
                        $total_quantity = "0";
                    }
                } else {
                    $total_quantity = "0";
                }

                $availableStock = ($product->stock - $total_quantity);

                $array = [
                    'status' => "1",
                    'details' => [
                        'product_id' => $product->product_id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'category_id' => $product->category_id,
                        'barcode' => $product->barcode,
                        'tags' => $product->tags,
                        'descriptions' => $product->descriptions,
                        'unit_type' => $product->unit_type,
                        'stock' => $product->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'currency_type' => $this->getSymbol($product->currency_type),
                        'minimum_stock' => $product->minimum_stock,
                        'notify_minimum_stock' => $product->notify_minimum_stock,
                        'force_moq' => $product->notify_minimum_stock,
                        'sale_price' => $product->sale_price,
                        'fob_price' => $product->fob_price,
                        'purchase_price' => $product->purchase_price,
                        'created_on' => $product->created_on,
                        'modified_on' => $product->modified_on,
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ]
                ];

                $array = $this->arrcheck($array);
                $this->response($array, 200);

            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }

        }
    }

    public function termsOfSalesList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];

            $list = $this->Apimodel->fetch_all_join("SELECT * FROM terms_of_sales WHERE term_status='1' and  `company_id`='" . $company_id . "' ORDER BY name ASC");

            if (!empty($list)) {
                foreach ($list as $termValue) {
                    $array[] = [
                        'term_id' => $termValue->term_id,
                        'language_id' => @$termValue->language_id,
                        'company_id' => @$termValue->company_id,
                        'user_id' => @$termValue->user_id,
                        'name' => @$termValue->name,
                        'created_at' => @$termValue->created_at,
                        'term_status' => @$termValue->term_status,
                        'default' => @$termValue->default
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function createCustomer_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['name'] = $this->post('name');
            $userData['business_name'] = $this->post('business_name');
            $userData['tax_id'] = $this->post('tax_id');
            $userData['discount'] = $this->post('discount');
            $userData['term_id'] = $this->post('term_id');
            $userData['group_id'] = $this->post('group_id');
            $userData['email'] = $this->post('email');
            $userData['phone'] = $this->post('phone');
            $userData['cell_phone'] = $this->post('cell_phone');
            $userData['notes'] = $this->post('notes');
            $userData['commercial_address'] = $this->post('commercial_address');
            $userData['commercial_delivery_address'] = $this->post('commercial_delivery_address');
            $userData['commercial_country'] = $this->post('commercial_country');
            $userData['commercial_state'] = $this->post('commercial_state');
            $userData['commercial_city'] = $this->post('commercial_city');
            $userData['commercial_zone'] = $this->post('commercial_zone');
            $userData['commercial_zip_code'] = $this->post('commercial_zip_code');
            $userData['dispatch_address'] = $this->post('dispatch_address');
            $userData['dispatch_delivery_address'] = $this->post('dispatch_delivery_address');
            $userData['dispatch_country'] = $this->post('dispatch_country');
            $userData['dispatch_state'] = $this->post('dispatch_state');
            $userData['dispatch_city'] = $this->post('dispatch_city');
            $userData['dispatch_zone'] = $this->post('dispatch_zone');
            $userData['dispatch_zip_code'] = $this->post('dispatch_zip_code');
            $userData['dispatch_shipping_notes'] = $this->post('notes');
            $userData['catalog_emails'] = $this->post('catalog_emails');
            $userData['cust_id_kor'] = $this->post('cust_id_kor');
            $userData['id_kor'] = $this->post('id_kor');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        //$this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
        $this->form_validation->set_rules('term_id', 'Terms Id', 'trim|required');
        $this->form_validation->set_rules('group_id', 'Group Id', 'trim|required');
        //$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[customers.email]');
        //$this->form_validation->set_rules('phone', 'phone', 'trim|required|numeric');
        //$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[customers.email]');
        //$this->form_validation->set_rules('phone', 'phone', 'trim|required|numeric');
        //$this->form_validation->set_message('is_unique', 'The %s is already taken');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('business_name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('business_name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('term_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('term_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('group_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('group_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('email')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('email'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('phone')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('phone'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            // Receive Emails = 1, Do not send Emails = 0

            $mydata = array(
                'language_id' => $userData['language_id'],
                'company_id' => $userData['company_id'],
                'user_id' => $userData['user_id'],
                'name' => $userData['name'],
                'business_name' => $userData['business_name'],
                'tax_id' => $userData['tax_id'],
                'discount' => $userData['discount'],
                'term_id' => $userData['term_id'],
                'group_id' => $userData['group_id'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'cell_phone' => $userData['cell_phone'],
                'notes' => $userData['notes'],
                'commercial_address' => $userData['commercial_address'],
                'commercial_delivery_address' => $userData['commercial_delivery_address'],
                'commercial_country' => $userData['commercial_country'],
                'commercial_state' => $userData['commercial_state'],
                'commercial_city' => $userData['commercial_city'],
                'commercial_zone' => $userData['commercial_zone'],
                'commercial_zip_code' => $userData['commercial_zip_code'],
                'dispatch_address' => $userData['dispatch_address'],
                'dispatch_delivery_address' => $userData['dispatch_delivery_address'],
                'dispatch_country' => $userData['dispatch_country'],
                'dispatch_state' => $userData['dispatch_state'],
                'dispatch_city' => $userData['dispatch_city'],
                'dispatch_zone' => $userData['dispatch_zone'],
                'dispatch_zip_code' => $userData['dispatch_zip_code'],
                'dispatch_shipping_notes' => $userData['notes'],
                'catalog_emails' => $userData['catalog_emails'],
                'customer_created_at' => date("Y-m-d H:i:s"),
                'cust_id_kor' => $userData['cust_id_kor'],
                'id_kor' => $userData['id_kor'],
                'customer_status' => 1
            );

            $insert = $this->Apimodel->add_details('customers', $mydata);

            $mydata = $this->arrcheck($mydata);

            if ($insert) {
                $this->response([
                    'status' => "1",
                    'message' => 'Customer created successfully.',
                    'info' => $mydata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => "Some problems occurred, please try again."
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function editCustomer_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['customer_id'] = $this->post('customer_id');
            $userData['name'] = $this->post('name');
            $userData['business_name'] = $this->post('business_name');
            $userData['tax_id'] = $this->post('tax_id');
            $userData['discount'] = $this->post('discount');
            $userData['term_id'] = $this->post('term_id');
            $userData['group_id'] = $this->post('group_id');
            $userData['email'] = $this->post('email');
            $userData['phone'] = $this->post('phone');
            $userData['cell_phone'] = $this->post('cell_phone');
            $userData['notes'] = $this->post('dispatch_shipping_notes');
            $userData['commercial_address'] = $this->post('commercial_address');
            $userData['commercial_delivery_address'] = $this->post('commercial_delivery_address');
            $userData['commercial_country'] = $this->post('commercial_country');
            $userData['commercial_state'] = $this->post('commercial_state');
            $userData['commercial_city'] = $this->post('commercial_city');
            $userData['commercial_zone'] = $this->post('commercial_zone');
            $userData['commercial_zip_code'] = $this->post('commercial_zip_code');
            $userData['dispatch_address'] = $this->post('dispatch_address');
            $userData['dispatch_delivery_address'] = $this->post('dispatch_delivery_address');
            $userData['dispatch_country'] = $this->post('dispatch_country');
            $userData['dispatch_state'] = $this->post('dispatch_state');
            $userData['dispatch_city'] = $this->post('dispatch_city');
            $userData['dispatch_zone'] = $this->post('dispatch_zone');
            $userData['dispatch_zip_code'] = $this->post('dispatch_zip_code');
            $userData['dispatch_shipping_notes'] = $this->post('dispatch_shipping_notes');
            $userData['catalog_emails'] = $this->post('catalog_emails');
        }

        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
        $this->form_validation->set_rules('term_id', 'Terms Id', 'trim|required');
        $this->form_validation->set_rules('group_id', 'Group Id', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        //$this->form_validation->set_rules('phone', 'phone', 'trim|required|numeric');

        if ($this->form_validation->run() === false) {
            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('business_name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('business_name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('term_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('term_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('group_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('group_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('email')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('email'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('phone')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('phone'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $customer_id = $userData['customer_id'];

            $numRows = $this->db->query("select * from `customers` where `customer_id` = '" . $customer_id . "'")->num_rows();

            if ($numRows == 0) {
                $this->response([
                    'status' => "0",
                    'error' => 'Invalid Customer'
                ], REST_Controller::HTTP_OK);
            } else {
                // Receive Emails = 1, Do not send Emails = 0

                $where = array(
                    'customer_id' => $customer_id
                );

                $mydata = array(
                    'name' => $userData['name'],
                    'business_name' => $userData['business_name'],
                    'tax_id' => $userData['tax_id'],
                    'discount' => $userData['discount'],
                    'term_id' => $userData['term_id'],
                    'group_id' => $userData['group_id'],
                    'phone' => $userData['phone'],
                    'cell_phone' => $userData['cell_phone'],
                    'notes' => $userData['dispatch_shipping_notes'],
                    'commercial_address' => $userData['commercial_address'],
                    'commercial_delivery_address' => $userData['commercial_delivery_address'],
                    'commercial_country' => $userData['commercial_country'],
                    'commercial_state' => $userData['commercial_state'],
                    'commercial_city' => $userData['commercial_city'],
                    'commercial_zone' => $userData['commercial_zone'],
                    'commercial_zip_code' => $userData['commercial_zip_code'],
                    'dispatch_address' => $userData['dispatch_address'],
                    'dispatch_delivery_address' => $userData['dispatch_delivery_address'],
                    'dispatch_country' => $userData['dispatch_country'],
                    'dispatch_state' => $userData['dispatch_state'],
                    'dispatch_city' => $userData['dispatch_city'],
                    'dispatch_zone' => $userData['dispatch_zone'],
                    'dispatch_zip_code' => $userData['dispatch_zip_code'],
                    'dispatch_shipping_notes' => $userData['dispatch_shipping_notes'],
                    'catalog_emails' => $userData['catalog_emails']
                );

                $update = $this->Apimodel->edit_single_row('customers', $mydata, $where);

                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Customer updated successfully.'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }

     public function editCustomerhv_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['customer_id'] = $this->post('customer_id');
            $userData['name'] = $this->post('name');
            $userData['business_name'] = $this->post('business_name');
            $userData['tax_id'] = $this->post('tax_id');
            $userData['discount'] = $this->post('discount');
            $userData['term_id'] = $this->post('term_id');
            $userData['group_id'] = $this->post('group_id');
            $userData['email'] = $this->post('email');
            $userData['phone'] = $this->post('phone');
            $userData['cell_phone'] = $this->post('cell_phone');
            $userData['notes'] = $this->post('dispatch_shipping_notes');
            $userData['commercial_address'] = $this->post('commercial_address');
            $userData['commercial_delivery_address'] = $this->post('commercial_delivery_address');
            $userData['commercial_country'] = $this->post('commercial_country');
            $userData['commercial_state'] = $this->post('commercial_state');
            $userData['commercial_city'] = $this->post('commercial_city');
            $userData['commercial_zone'] = $this->post('commercial_zone');
            $userData['commercial_zip_code'] = $this->post('commercial_zip_code');
            $userData['dispatch_address'] = $this->post('dispatch_address');
            $userData['dispatch_delivery_address'] = $this->post('dispatch_delivery_address');
            $userData['dispatch_country'] = $this->post('dispatch_country');
            $userData['dispatch_state'] = $this->post('dispatch_state');
            $userData['dispatch_city'] = $this->post('dispatch_city');
            $userData['dispatch_zone'] = $this->post('dispatch_zone');
            $userData['dispatch_zip_code'] = $this->post('dispatch_zip_code');
            $userData['dispatch_shipping_notes'] = $this->post('dispatch_shipping_notes');
            $userData['catalog_emails'] = $this->post('catalog_emails');
        }

        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
        $this->form_validation->set_rules('term_id', 'Terms Id', 'trim|required');
        $this->form_validation->set_rules('group_id', 'Group Id', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('phone', 'phone', 'trim|required|numeric');

        if ($this->form_validation->run() === false) {
            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('business_name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('business_name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('term_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('term_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('group_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('group_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('email')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('email'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('phone')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('phone'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $customer_id = $userData['customer_id'];

            $numRows = $this->db->query("select * from `customers` where `customer_id` = '" . $customer_id . "'")->num_rows();

            if ($numRows == 0) {
                $this->response([
                    'status' => "0",
                    'error' => 'Invalid Customer'
                ], REST_Controller::HTTP_OK);
            } else {
                // Receive Emails = 1, Do not send Emails = 0

                $where = array(
                    'customer_id' => $customer_id
                );

                $mydata = array(
                    'name' => $userData['name'],
                    'business_name' => $userData['business_name'],
                    'tax_id' => $userData['tax_id'],
                    'discount' => $userData['discount'],
                    'term_id' => $userData['term_id'],
                    'group_id' => $userData['group_id'],
                    'phone' => $userData['phone'],
                    'cell_phone' => $userData['cell_phone'],
                    'notes' => $userData['dispatch_shipping_notes'],
                    'commercial_address' => $userData['commercial_address'],
                    'commercial_delivery_address' => $userData['commercial_delivery_address'],
                    'commercial_country' => $userData['commercial_country'],
                    'commercial_state' => $userData['commercial_state'],
                    'commercial_city' => $userData['commercial_city'],
                    'commercial_zone' => $userData['commercial_zone'],
                    'commercial_zip_code' => $userData['commercial_zip_code'],
                    'dispatch_address' => $userData['dispatch_address'],
                    'dispatch_delivery_address' => $userData['dispatch_delivery_address'],
                    'dispatch_country' => $userData['dispatch_country'],
                    'dispatch_state' => $userData['dispatch_state'],
                    'dispatch_city' => $userData['dispatch_city'],
                    'dispatch_zone' => $userData['dispatch_zone'],
                    'dispatch_zip_code' => $userData['dispatch_zip_code'],
                    'dispatch_shipping_notes' => $userData['dispatch_shipping_notes'],
                    'catalog_emails' => $userData['catalog_emails']
                );

                $update = $this->Apimodel->edit_single_row('customers', $mydata, $where);

                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Customer updated successfully.'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }

    public function addcustomer_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['name'] = $this->post('name');
            $userData['business_name'] = $this->post('business_name');
            $userData['tax_id'] = $this->post('tax_id');
            $userData['discount'] = $this->post('discount');
            $userData['term_id'] = $this->post('term_id');
            $userData['group_id'] = $this->post('group_id');
            $userData['email'] = $this->post('email');
            $userData['phone'] = $this->post('phone');
            $userData['cell_phone'] = $this->post('cell_phone');
            $userData['notes'] = $this->post('notes');
            $userData['commercial_address'] = $this->post('commercial_address');
            $userData['commercial_delivery_address'] = $this->post('commercial_delivery_address');
            $userData['commercial_country'] = $this->post('commercial_country');
            $userData['commercial_state'] = $this->post('commercial_state');
            $userData['commercial_city'] = $this->post('commercial_city');
            $userData['commercial_zone'] = $this->post('commercial_zone');
            $userData['commercial_zip_code'] = $this->post('commercial_zip_code');
            $userData['dispatch_address'] = $this->post('commercial_address');
            $userData['dispatch_delivery_address'] = $this->post('commercial_delivery_address');
            $userData['dispatch_country'] = $this->post('commercial_country');
            $userData['dispatch_state'] = $this->post('commercial_state');
            $userData['dispatch_city'] = $this->post('commercial_city');
            $userData['dispatch_zone'] = $this->post('commercial_zone');
            $userData['dispatch_zip_code'] = $this->post('commercial_zip_code');
            $userData['dispatch_shipping_notes'] = $this->post('notes');
            $userData['catalog_emails'] = $this->post('catalog_emails');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('business_name', 'Business Name', 'trim|required');
        $this->form_validation->set_rules('term_id', 'Terms Id', 'trim|required');
        $this->form_validation->set_rules('group_id', 'Group Id', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[customers.email]');
        $this->form_validation->set_rules('phone', 'phone', 'trim|required|numeric');
        $this->form_validation->set_message('is_unique', 'The %s is already taken');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('business_name')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('business_name'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('term_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('term_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('group_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('group_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('email')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('email'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('phone')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('phone'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            // Receive Emails = 1, Do not send Emails = 0

            $mydata = array(
                'language_id' => $userData['language_id'],
                'company_id' => $userData['company_id'],
                'user_id' => $userData['user_id'],
                'name' => $userData['name'],
                'business_name' => $userData['business_name'],
                'tax_id' => $userData['tax_id'],
                'discount' => $userData['discount'],
                'term_id' => $userData['term_id'],
                'group_id' => $userData['group_id'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'cell_phone' => $userData['cell_phone'],
                'notes' => $userData['notes'],
                'commercial_address' => $userData['commercial_address'],
                'commercial_delivery_address' => $userData['commercial_delivery_address'],
                'commercial_country' => $userData['commercial_country'],
                'commercial_state' => $userData['commercial_state'],
                'commercial_city' => $userData['commercial_city'],
                'commercial_zone' => $userData['commercial_zone'],
                'commercial_zip_code' => $userData['commercial_zip_code'],
                'dispatch_address' => $userData['dispatch_address'],
                'dispatch_delivery_address' => $userData['dispatch_delivery_address'],
                'dispatch_country' => $userData['dispatch_country'],
                'dispatch_state' => $userData['dispatch_state'],
                'dispatch_city' => $userData['dispatch_city'],
                'dispatch_zone' => $userData['dispatch_zone'],
                'dispatch_zip_code' => $userData['dispatch_zip_code'],
                'dispatch_shipping_notes' => $userData['notes'],
                'catalog_emails' => $userData['catalog_emails'],
                'customer_created_at' => date("Y-m-d H:i:s"),
                'customer_status' => 1
            );

            $insert = $this->Apimodel->add_details('customers', $mydata);

            $mydata = $this->arrcheck($mydata);

            if ($insert) {
                $this->response([
                    'status' => "1",
                    'message' => 'Customer created successfully.',
                    'info' => $mydata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => "Some problems occurred, please try again."
                ], REST_Controller::HTTP_OK);
            }
        }


    }

    public function customerList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $company_id = $userData['company_id'];

            $userp = $this->Apimodel->fetch_all_join("SELECT * FROM vendors WHERE user_id =" . $user_id . " AND company_id = " . $company_id . "");

            $allCustomers = 0;

            if (!empty($userp)) {
                foreach ($userp as $rp) {
                    $allCustomers = $rp->all_customers;
                }
            }

            if ($allCustomers != 0) {
                $getCustomerSql = "SELECT 'allcustomer:yes' as selection, ct.*, cg.name as group_name, cg.percentage_on_price, cg.percent_price_amount, ts.name as term_name from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE ct.company_id ='" . $company_id . "' AND ct.customer_status = 1 ORDER BY TRIM(ct.name) ASC ";

            } else {

                $getCustomerSql = "SELECT 'allcustomer:no' as selection,ct.*, cg.name as group_name, cg.percentage_on_price, cg.percent_price_amount, ts.name as term_name from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE ct.company_id ='".$company_id."' AND  FIND_IN_SET('".$user_id."',ct.user_id) AND ct.customer_status = 1 ORDER BY TRIM(ct.name) ASC ";
            }


            $list = $this->Apimodel->fetch_all_join($getCustomerSql);

            if (!empty($list)) {
                foreach ($list as $customerValue) {
                    $array[] = [
                        'selection' => $customerValue->selection,
                        'customer_id' => $customerValue->customer_id,
                        'language_id' => $customerValue->language_id,
                        'company_id' => $customerValue->company_id,
                        'user_id' => $customerValue->user_id,
                        'name' => $customerValue->name,
                        'business_name' => $customerValue->business_name,
                        'tax_id' => $customerValue->tax_id,
                        'balance' => number_format($customerValue->balance, 2),
                        'discount' => $customerValue->discount,
                        'term_id' => $customerValue->term_id,
                        'term_name' => $customerValue->term_name,
                        'group_id' => $customerValue->group_id,
                        'group_name' => $customerValue->group_name,
                        'percentage_on_price' => $customerValue->percentage_on_price,
                        'percent_price_amount' => $customerValue->percent_price_amount,
                        'email' => $customerValue->email,
                        'phone' => $customerValue->phone,
                        'cell_phone' => $customerValue->cell_phone,
                        'notes' => $customerValue->dispatch_shipping_notes,
                        'commercial_address' => $customerValue->commercial_address,
                        'commercial_delivery_address' => $customerValue->commercial_delivery_address,
                        'commercial_country' => $customerValue->commercial_country,
                        'commercial_state' => $customerValue->commercial_state,
                        'commercial_city' => $customerValue->commercial_city,
                        'commercial_zone' => $customerValue->commercial_zone,
                        'commercial_zip_code' => $customerValue->commercial_zip_code,
                        'dispatch_address' => $customerValue->dispatch_address,
                        'dispatch_delivery_address' => $customerValue->dispatch_delivery_address,
                        'dispatch_country' => $customerValue->dispatch_country,
                        'dispatch_state' => $customerValue->dispatch_state,
                        'dispatch_city' => $customerValue->dispatch_city,
                        'dispatch_zone' => $customerValue->dispatch_zone,
                        'dispatch_zip_code' => $customerValue->dispatch_zip_code,
                        'dispatch_shipping_notes' => $customerValue->dispatch_shipping_notes,
                        'catalog_emails' => $customerValue->catalog_emails,
                        'customer_created_at' => $customerValue->customer_created_at,
                        'customer_status' => $customerValue->customer_status
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function softcustomerList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }


        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];


            $getCustomerSql = "SELECT 'allcustomer:yes' as selection, ct.*, cg.name as group_name, cg.percentage_on_price, cg.percent_price_amount, ts.name as term_name from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE ct.company_id ='" . $company_id . "' AND modified_at>=DATE_SUB(CURDATE(), INTERVAL 2 DAY)  ORDER BY `business_name` ASC ";


            $list = $this->Apimodel->fetch_all_join($getCustomerSql);

            if (!empty($list)) {
                foreach ($list as $customerValue) {
                    $array[] = [
                        'customer_id' => $customerValue->customer_id,
                        'cust_id_kor' => $customerValue->cust_id_kor,
                        'id_kor' => $customerValue->id_kor,
                        'language_id' => $customerValue->language_id,
                        'company_id' => $customerValue->company_id,
                        'user_id' => $customerValue->user_id,
                        'name' => $customerValue->name,
                        'business_name' => $customerValue->business_name,
                        'tax_id' => $customerValue->tax_id,
                        'balance' => number_format($customerValue->balance, 2),
                        'discount' => $customerValue->discount,
                        'term_id' => $customerValue->term_id,
                        'term_name' => $customerValue->term_name,
                        'group_id' => $customerValue->group_id,
                        'group_name' => $customerValue->group_name,
                        'percentage_on_price' => $customerValue->percentage_on_price,
                        'percent_price_amount' => $customerValue->percent_price_amount,
                        'email' => $customerValue->email,
                        'phone' => $customerValue->phone,
                        'cell_phone' => $customerValue->cell_phone,
                        'notes' => $customerValue->dispatch_shipping_notes,
                        'commercial_address' => $customerValue->commercial_address,
                        'commercial_delivery_address' => $customerValue->commercial_delivery_address,
                        'commercial_country' => $customerValue->commercial_country,
                        'commercial_state' => $customerValue->commercial_state,
                        'commercial_city' => $customerValue->commercial_city,
                        'commercial_zone' => $customerValue->commercial_zone,
                        'commercial_zip_code' => $customerValue->commercial_zip_code,
                        'dispatch_address' => $customerValue->dispatch_address,
                        'dispatch_delivery_address' => $customerValue->dispatch_delivery_address,
                        'dispatch_country' => $customerValue->dispatch_country,
                        'dispatch_state' => $customerValue->dispatch_state,
                        'dispatch_city' => $customerValue->dispatch_city,
                        'dispatch_zone' => $customerValue->dispatch_zone,
                        'dispatch_zip_code' => $customerValue->dispatch_zip_code,
                        'dispatch_shipping_notes' => $customerValue->dispatch_shipping_notes,
                        'catalog_emails' => $customerValue->catalog_emails,
                        'customer_created_at' => $customerValue->customer_created_at,
                        'modified_at' => $customerValue->modified_at,
                        'customer_status' => $customerValue->customer_status
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function wwsoftcustomerList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }


        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];


            $getCustomerSql = "SELECT 'allcustomer:yes' as selection, ct.*, cg.name as group_name, cg.percentage_on_price, cg.percent_price_amount, ts.name as term_name from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE ct.company_id ='" . $company_id . "' ORDER BY `business_name` ASC ";


            $list = $this->Apimodel->fetch_all_join($getCustomerSql);

            if (!empty($list)) {
                foreach ($list as $customerValue) {
                    $array[] = [
                        'customer_id' => $customerValue->customer_id,
                        'cust_id_kor' => $customerValue->cust_id_kor,
                        'id_kor' => $customerValue->id_kor,
                        'language_id' => $customerValue->language_id,
                        'company_id' => $customerValue->company_id,
                        'user_id' => $customerValue->user_id,
                        'name' => $customerValue->name,
                        'business_name' => $customerValue->business_name,
                        'tax_id' => $customerValue->tax_id,
                        'balance' => number_format($customerValue->balance, 2),
                        'discount' => $customerValue->discount,
                        'term_id' => $customerValue->term_id,
                        'term_name' => $customerValue->term_name,
                        'group_id' => $customerValue->group_id,
                        'group_name' => $customerValue->group_name,
                        'percentage_on_price' => $customerValue->percentage_on_price,
                        'percent_price_amount' => $customerValue->percent_price_amount,
                        'email' => $customerValue->email,
                        'phone' => $customerValue->phone,
                        'cell_phone' => $customerValue->cell_phone,
                        'notes' => $customerValue->dispatch_shipping_notes,
                        'commercial_address' => $customerValue->commercial_address,
                        'commercial_delivery_address' => $customerValue->commercial_delivery_address,
                        'commercial_country' => $customerValue->commercial_country,
                        'commercial_state' => $customerValue->commercial_state,
                        'commercial_city' => $customerValue->commercial_city,
                        'commercial_zone' => $customerValue->commercial_zone,
                        'commercial_zip_code' => $customerValue->commercial_zip_code,
                        'dispatch_address' => $customerValue->dispatch_address,
                        'dispatch_delivery_address' => $customerValue->dispatch_delivery_address,
                        'dispatch_country' => $customerValue->dispatch_country,
                        'dispatch_state' => $customerValue->dispatch_state,
                        'dispatch_city' => $customerValue->dispatch_city,
                        'dispatch_zone' => $customerValue->dispatch_zone,
                        'dispatch_zip_code' => $customerValue->dispatch_zip_code,
                        'dispatch_shipping_notes' => $customerValue->dispatch_shipping_notes,
                        'catalog_emails' => $customerValue->catalog_emails,
                        'customer_created_at' => $customerValue->customer_created_at,
                        'modified_at' => $customerValue->modified_at,
                        'customer_status' => $customerValue->customer_status
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function synccustomerList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $company_id = $userData['company_id'];


            $getCustomerSql = "SELECT ct.*, cg.name as group_name, cg.percentage_on_price, cg.percent_price_amount, ts.name as term_name from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE ct.company_id ='" . $company_id . "' ORDER BY `business_name` ASC ";

            $list = $this->Apimodel->fetch_all_join($getCustomerSql);

            if (!empty($list)) {
                foreach ($list as $customerValue) {
                    $array[] = [
                        'customer_id' => $customerValue->customer_id,
                        'language_id' => $customerValue->language_id,
                        'company_id' => $customerValue->company_id,
                        'user_id' => $customerValue->user_id,
                        'name' => $customerValue->name,
                        'business_name' => $customerValue->business_name,
                        'tax_id' => $customerValue->tax_id,
                        'balance' => number_format($customerValue->balancec, 2),
                        'discount' => $customerValue->discount,
                        'term_id' => $customerValue->term_id,
                        'term_name' => $customerValue->term_name,
                        'group_id' => $customerValue->group_id,
                        'group_name' => $customerValue->group_name,
                        'percentage_on_price' => $customerValue->percentage_on_price,
                        'percent_price_amount' => $customerValue->percent_price_amount,
                        'email' => $customerValue->email,
                        'phone' => $customerValue->phone,
                        'cell_phone' => $customerValue->cell_phone,
                        'notes' => $customerValue->dispatch_shipping_notes,
                        'commercial_address' => $customerValue->commercial_address,
                        'commercial_delivery_address' => $customerValue->commercial_delivery_address,
                        'commercial_country' => $customerValue->commercial_country,
                        'commercial_state' => $customerValue->commercial_state,
                        'commercial_city' => $customerValue->commercial_city,
                        'commercial_zone' => $customerValue->commercial_zone,
                        'commercial_zip_code' => $customerValue->commercial_zip_code,
                        'dispatch_address' => $customerValue->dispatch_address,
                        'dispatch_delivery_address' => $customerValue->dispatch_delivery_address,
                        'dispatch_country' => $customerValue->dispatch_country,
                        'dispatch_state' => $customerValue->dispatch_state,
                        'dispatch_city' => $customerValue->dispatch_city,
                        'dispatch_zone' => $customerValue->dispatch_zone,
                        'dispatch_zip_code' => $customerValue->dispatch_zip_code,
                        'dispatch_shipping_notes' => $customerValue->dispatch_shipping_notes,
                        'catalog_emails' => $customerValue->catalog_emails,
                        'customer_created_at' => $customerValue->customer_created_at,
                        'customer_status' => $customerValue->customer_status
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function warehousecustomerList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['user_id'] = $this->post('user_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $company_id = $userData['company_id'];


            $getCustomerSql = "SELECT ct.*, cg.name as group_name, cg.percentage_on_price, cg.percent_price_amount, ts.name as term_name from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE ct.company_id ='" . $company_id . "' ORDER BY `business_name` ASC ";

            $list = $this->Apimodel->fetch_all_join($getCustomerSql);

            if (!empty($list)) {
                foreach ($list as $customerValue) {
                    $array[] = [
                        'customer_id' => $customerValue->customer_id,
                        'language_id' => $customerValue->language_id,
                        'company_id' => $customerValue->company_id,
                        'user_id' => $customerValue->user_id,
                        'name' => $customerValue->name,
                        'business_name' => $customerValue->business_name,
                        'tax_id' => $customerValue->tax_id,
                        'balance' => number_format($customerValue->balancec, 2),
                        'discount' => $customerValue->discount,
                        'term_id' => $customerValue->term_id,
                        'term_name' => $customerValue->term_name,
                        'group_id' => $customerValue->group_id,
                        'group_name' => $customerValue->group_name,
                        'percentage_on_price' => $customerValue->percentage_on_price,
                        'percent_price_amount' => $customerValue->percent_price_amount,
                        'email' => $customerValue->email,
                        'phone' => $customerValue->phone,
                        'cell_phone' => $customerValue->cell_phone,
                        'notes' => $customerValue->dispatch_shipping_notes,
                        'commercial_address' => $customerValue->commercial_address,
                        'commercial_delivery_address' => $customerValue->commercial_delivery_address,
                        'commercial_country' => $customerValue->commercial_country,
                        'commercial_state' => $customerValue->commercial_state,
                        'commercial_city' => $customerValue->commercial_city,
                        'commercial_zone' => $customerValue->commercial_zone,
                        'commercial_zip_code' => $customerValue->commercial_zip_code,
                        'dispatch_address' => $customerValue->dispatch_address,
                        'dispatch_delivery_address' => $customerValue->dispatch_delivery_address,
                        'dispatch_country' => $customerValue->dispatch_country,
                        'dispatch_state' => $customerValue->dispatch_state,
                        'dispatch_city' => $customerValue->dispatch_city,
                        'dispatch_zone' => $customerValue->dispatch_zone,
                        'dispatch_zip_code' => $customerValue->dispatch_zip_code,
                        'dispatch_shipping_notes' => $customerValue->dispatch_shipping_notes,
                        'catalog_emails' => $customerValue->catalog_emails,
                        'customer_created_at' => $customerValue->customer_created_at,
                        'customer_status' => $customerValue->customer_status
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function customerDetails_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['customer_id'] = $this->post('customer_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $customer_id = $userData['customer_id'];

            $getCustomerSql = "SELECT ct.*, cg.name as group_name, ts.name as term_name, cg.percentage_on_price,cg.percent_price_amount from `customers` as ct
				LEFT JOIN `customer_groups` as cg ON cg.group_id = ct.group_id
				LEFT JOIN `terms_of_sales` as ts ON ts.term_id = ct.term_id
				WHERE  ct.language_id = '" . $language_id . "' AND ct.customer_id = '" . $customer_id . "'";

            $customerValue = $this->Apimodel->fetch_single_join($getCustomerSql);

            if (!empty($customerValue)) {
                $array[] = [
                    'customer_id' => $customerValue->customer_id,
                    'language_id' => $customerValue->language_id,
                    'company_id' => $customerValue->company_id,
                    'user_id' => $customerValue->user_id,
                    'name' => $customerValue->name,
                    'business_name' => $customerValue->business_name,
                    'tax_id' => $customerValue->tax_id,
                    'discount' => $customerValue->discount,
                    'term_id' => $customerValue->term_id,
                    'term_name' => $customerValue->term_name,
                    'group_id' => $customerValue->group_id,
                    'group_name' => $customerValue->group_name,
                    'percentage_on_price' => $customerValue->percentage_on_price,
                    'percent_price_amount' => $customerValue->percent_price_amount,
                    'email' => $customerValue->email,
                    'phone' => $customerValue->phone,
                    'cell_phone' => $customerValue->cell_phone,
                    'notes' => $customerValue->dispatch_shipping_notes,
                    'commercial_address' => $customerValue->commercial_address,
                    'commercial_delivery_address' => $customerValue->commercial_delivery_address,
                    'commercial_country' => $customerValue->commercial_country,
                    'commercial_state' => $customerValue->commercial_state,
                    'commercial_city' => $customerValue->commercial_city,
                    'commercial_zone' => $customerValue->commercial_zone,
                    'commercial_zip_code' => $customerValue->commercial_zip_code,
                    'dispatch_address' => $customerValue->dispatch_address,
                    'dispatch_delivery_address' => $customerValue->dispatch_delivery_address,
                    'dispatch_country' => $customerValue->dispatch_country,
                    'dispatch_state' => $customerValue->dispatch_state,
                    'dispatch_city' => $customerValue->dispatch_city,
                    'dispatch_zone' => $customerValue->dispatch_zone,
                    'dispatch_zip_code' => $customerValue->dispatch_zip_code,
                    'dispatch_shipping_notes' => $customerValue->dispatch_shipping_notes,
                    'catalog_emails' => $customerValue->catalog_emails,
                    'customer_created_at' => $customerValue->customer_created_at,
                    'customer_status' => $customerValue->customer_status
                ];

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'details' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function customerDelete_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $customer_id = $userData['customer_id'];

            $numRows = $this->db->query("select * from `customers` where `customer_id` = '" . $customer_id . "'")->num_rows();

            if ($numRows == 0) {
                $this->response([
                    'status' => "0",
                    'error' => 'Invalid Customer'
                ], REST_Controller::HTTP_OK);
            } else {

                $where = array(
                    'customer_id' => $customer_id
                );

                $delete = $this->Apimodel->delete_single_con('customers', $where);

                if ($delete) {

                    $this->response([
                        'status' => "1",
                        'message' => 'Customer deleted successfully'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => 'Opps, Some thing went wrong'
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }

    public function adminSettings_get()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_GET = (array)$obj;
            $userData = $_GET;
        }

        $checkSetting = $this->Apimodel->get_cond('settings', "settingId='1'");

        if (!empty($checkSetting)) {
            if ($checkSetting->logo != "") {
                $pic = base_url() . 'uploads/logos/' . $checkSetting->logo;
            } else {
                $pic = base_url() . 'frontend/assets/no-image.jpg';
            }

            $array['info'] = [
                'settingId' => $checkSetting->settingId,
                'title' => $checkSetting->title,
                'address' => $checkSetting->address,
                'email' => $checkSetting->email,
                'phone' => $checkSetting->phone,
                'maxPrice' => $checkSetting->maxPrice,
                'maxServiceArea' => $checkSetting->maxServiceArea,
                'logo' => $pic

            ];
            $array = $this->arrcheck($array);
            $this->response($array, 200);

        } else {
            $this->response([
                'status' => "0",
                'error' => 'No details found'
            ], REST_Controller::HTTP_NOT_FOUND);

        }
    }

    public function generateOtp_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData["email"] = $this->post('email');
            $userData['user_type'] = $this->post('user_type');
        }

        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('user_type', 'user_type', 'required');

        if ($this->form_validation->run() === false) {
            if (form_error('email')) {
                $this->response(array(
                    'status' => '0',
                    'error' => strip_tags(form_error('email'))
                ), REST_Controller::HTTP_OK);
            }

            if (form_error('user_type')) {
                $this->response(array(
                    'status' => '0',
                    'error' => strip_tags(form_error('user_type'))
                ), REST_Controller::HTTP_OK);
            }

        } else {
            $email = $userData['email'];
            $where = "email = '$email'";
            if ($userData['user_type'] == '1') {
                $dataraw = $this->Apimodel->get_cond("vendors", $where);
            } else {
                $dataraw = $this->Apimodel->get_cond("warehouse_users", $where);
            }

            if (!empty($dataraw)) {

                $random_number = $this->generate_otp(6);

                $updatedata = array(
                    'otp' => $random_number,
                );

                if ($userData['user_type'] == '1') {
                    $update = $this->Apimodel->update_cond("vendors", $where, $updatedata);
                } else {
                    $update = $this->Apimodel->update_cond("warehouse_users", $where, $updatedata);
                }

                $to = $email;
                // Sent Email to Vendor
                $from = 'no_reply@goigi.in';
                $subject = 'New otp from FULLVENDOR';
                $loginPath = base_url();
                $imagePath = base_url() . 'images/logo_2.png';

                $message = "<table width='100%' border='0' align='center' cellpadding='0' cellspacing='0'>
				<tbody>
				<tr>
				<td align='center'>
				<table class='col-600' width='600' border='0' align='center' cellpadding='0' cellspacing='0' style='margin-left:20px; margin-right:20px; border-left: 1px solid #dbd9d9; border-right: 1px solid #dbd9d9; border-top:2px solid #232323'>
				<tbody>
				<tr>
				<td height='35'></td>
				</tr>
				<tr>
				<td align='center' style='padding:5px 10px;font-family: Raleway, sans-serif; font-size:16px; font-weight: bold; color:#2a3a4b;'><img src='" . $imagePath . "' style='width:120px;' /></td>
				</tr>
				<tr>
				<td height='35'></td>
				</tr>
				<tr>
				<td align='left' style='padding:5px 10px;font-family: Raleway, sans-serif; font-size:16px; font-weight: bold; color:#2a3a4b;'>Hello " . $email . ",</td>
				</tr>
				<tr>
				<td height='10'></td>
				</tr>
				<tr>
				<td align='left' style='padding:5px 10px;font-family: Lato, sans-serif; font-size:16px; color:#444; line-height:24px; font-weight: 400;'>
				New otp from  <strong style='font-weight:bold;'>FULLVENDOR</strong>.
				</td>
				</tr>
				</tbody>
				</table>
				</td>
				</tr>
				<tr>
				<td align='center'>
				<table class='col-600' width='600' border='0' align='center' cellpadding='0' cellspacing='0' style='margin-left:20px; margin-right:20px; border-left: 1px solid #dbd9d9; border-right: 1px solid #dbd9d9; border-bottom:2px solid #232323'>
				<tbody>
				<tr>
				<td height='10'></td>
				</tr>

				<tr>
				<td align='left' style='padding:5px 10px;font-family: Lato, sans-serif; font-size:16px; color:#444; line-height:24px; font-weight: bold;'>
				OTP: " . $random_number . "
				</td>
				</tr>
				<tr>
				<td height='10'><p> Never Share your OTP with anyone. Sharing these details can lead to unauthorised access to your account.</p></td>
				</tr>
				<tr>
				<td height='30'></td>
				</tr>
				<tr>
				<td align='left' style='padding:0 10px;font-family: Lato, sans-serif; font-size:16px; color:#232323; line-height:24px; font-weight: 700;'>
				Thank you!
				</td>
				</tr>
				<tr>
				<td align='left' style='padding:0 10px;font-family: Lato, sans-serif; font-size:14px; color:#232323; line-height:24px; font-weight: 700;'>
				Sincerely
				</td>
				</tr>
				<tr>
				<td align='left' style='padding:0 10px;font-family: Lato, sans-serif; font-size:14px; color:#232323; line-height:24px; font-weight: 700;'>
				Team FULLVENDOR
				</td>
				</tr>
				</tbody>
				</table>
				</td>
				</tr>
				</tbody>
				</table>";

                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

                if (mail($email, $subject, $message, $headers)) {


                    $this->response(array(
                        'status' => '1',
                        'userId' => $dataraw->user_id,
                        'otp' => $random_number,
                        'message' => 'OTP Sent successfully.'
                    ), REST_Controller::HTTP_OK);
                } else {
                    $this->response(array(
                        'status' => '0',
                        'message' => 'Oops! Something went wrong while trying to send your email.'
                    ), REST_Controller::HTTP_OK);
                }

            } else {
                $this->response(array(
                    'status' => '0',
                    'message' => 'Email id does not exist!.'
                ), REST_Controller::HTTP_OK);
            }
        }
    }

    public function verifiedOtp_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['email'] = $this->post('email');
            $userData['user_type'] = $this->post('user_type');
            $userData['otp'] = $this->post('otp');
            $userData['password'] = $this->post('password');
        }

        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('user_type', 'user_type', 'required');
        $this->form_validation->set_rules('otp', 'otp', 'required');
        $this->form_validation->set_rules('password', 'password', 'required');

        if ($this->form_validation->run() === false) {
            if (form_error('email')) {
                $this->response(array(
                    'status' => '0',
                    'error' => strip_tags(form_error('email'))
                ), REST_Controller::HTTP_OK);
            }
            if (form_error('user_type')) {
                $this->response(array(
                    'status' => '0',
                    'error' => strip_tags(form_error('user_type'))
                ), REST_Controller::HTTP_OK);
            }
            if (form_error('otp')) {
                $this->response(array(
                    'status' => '0',
                    'error' => strip_tags(form_error('otp'))
                ), REST_Controller::HTTP_OK);
            }
            if (form_error('password')) {
                $this->response(array(
                    'status' => '0',
                    'error' => strip_tags(form_error('password'))
                ), REST_Controller::HTTP_OK);
            }
        } else {

            $email = $userData['email'];
            $otp = $userData['otp'];
            $password = $userData['password'];
            $where = "email = '$email'";

            if ($userData['user_type'] == '1') {
                $dataraw = $this->Apimodel->get_cond("vendors", $where);
            } else {
                $dataraw = $this->Apimodel->get_cond("warehouse_users", $where);
            }


            if ($dataraw) {

                if ($userData['user_type'] == '1') {
                    $validateOtp = $this->Apimodel->validateOtp("vendors", $email, $otp);
                } else {
                    $validateOtp = $this->Apimodel->validateOtp("warehouse_users", $email, $otp);
                }

                if ($validateOtp) {
                    $updatedata = array(
                        'password' => $this->enc_password($password),
                    );
                    if ($userData['user_type'] == '1') {

                        $update = $this->Apimodel->update_cond("vendors", $where, $updatedata);
                    } else {
                        $update = $this->Apimodel->update_cond("warehouse_users", $where, $updatedata);
                    }

                    if ($update) {

                        $to = $email;
                        // Sent Email to Vendor
                        $from = 'no_reply@goigi.in';
                        $subject = 'Successful Account password has been changed of FULLVENDOR';
                        $loginPath = base_url();
                        $imagePath = base_url() . 'images/logo_2.png';

                        $htmlContent = "<table width='100%' border='0' align='center' cellpadding='0' cellspacing='0'>
						<tbody>
						<tr>
						<td align='center'>
						<table class='col-600' width='600' border='0' align='center' cellpadding='0' cellspacing='0' style='margin-left:20px; margin-right:20px; border-left: 1px solid #dbd9d9; border-right: 1px solid #dbd9d9; border-top:2px solid #232323'>
						<tbody>
						<tr>
						<td height='35'></td>
						</tr>
						<tr>
						<td align='center' style='padding:5px 10px;font-family: Raleway, sans-serif; font-size:16px; font-weight: bold; color:#2a3a4b;'><img src='" . $imagePath . "' style='width:120px;' /></td>
						</tr>
						<tr>
						<td height='35'></td>
						</tr>
						<tr>
						<td align='left' style='padding:5px 10px;font-family: Raleway, sans-serif; font-size:16px; font-weight: bold; color:#2a3a4b;'>Hello " . $email . ",</td>
						</tr>
						<tr>
						<td height='10'></td>
						</tr>
						<tr>
						<td align='left' style='padding:5px 10px;font-family: Lato, sans-serif; font-size:16px; color:#444; line-height:24px; font-weight: 400;'>
						Your password is successfully reset from  <strong style='font-weight:bold;'>FULLVENDOR</strong>.
						</td>
						</tr>
						</tbody>
						</table>
						</td>
						</tr>
						<tr>
						<td align='center'>
						<table class='col-600' width='600' border='0' align='center' cellpadding='0' cellspacing='0' style='margin-left:20px; margin-right:20px; border-left: 1px solid #dbd9d9; border-right: 1px solid #dbd9d9; border-bottom:2px solid #232323'>
						<tbody>
						<tr>
						<td height='10'></td>
						</tr>
						<tr>
						<td height='10'></td>
						</tr>
						<tr>
						<td align='left' style='padding:5px 10px;font-family: Lato, sans-serif; font-size:16px; color:#444; line-height:24px; font-weight: bold;'>
						Email: " . $email . "<br>
						Password: " . $password . "
						</td>
						</tr>
						<tr>
						<td height='10'></td>
						</tr>
						<tr>
						<td height='10'></td>
						</tr>
						<tr>
						<td height='30'></td>
						</tr>
						<tr>
						<td align='left' style='padding:0 10px;font-family: Lato, sans-serif; font-size:16px; color:#232323; line-height:24px; font-weight: 700;'>
						Thank you!
						</td>
						</tr>
						<tr>
						<td align='left' style='padding:0 10px;font-family: Lato, sans-serif; font-size:14px; color:#232323; line-height:24px; font-weight: 700;'>
						Sincerely
						</td>
						</tr>
						<tr>
						<td align='left' style='padding:0 10px;font-family: Lato, sans-serif; font-size:14px; color:#232323; line-height:24px; font-weight: 700;'>
						Team FULLVENDOR
						</td>
						</tr>
						</tbody>
						</table>
						</td>
						</tr>
						</tbody>
						</table>";

                        $this->load->library('email');
                        $this->email->set_newline("\r\n");
                        $this->email->from('no-reply@fullvendor.net', 'FULLVENDOR');
                        $this->email->to($email);
                        $this->email->subject($subject);
                        $this->email->message($htmlContent);
                        $this->email->set_mailtype("html");

                        $updateotpdata = array(
                            'otp' => '',
                        );

                        if ($userData['user_type'] == '1') {

                            $update = $this->Apimodel->update_cond("vendors", $where, $updateotpdata);
                        } else {
                            $update = $this->Apimodel->update_cond("warehouse_users", $where, $updateotpdata);
                        }


                        if ($this->email->send()) {
                            $this->response(array(
                                'status' => "1",
                                'userId' => $dataraw->user_id,
                                'Password' => $password,
                                'message' => 'Password reset successfully.'
                            ), REST_Controller::HTTP_OK);
                        } else {
                            $this->response(array(
                                'status' => "0",
                                'message' => 'Sorry! try to Resend the later'
                            ), REST_Controller::HTTP_OK);
                        }
                    } else {
                        $this->response("Some problems occurred, please try again.", REST_Controller::HTTP_OK);
                    }
                } else {
                    $this->response(array(
                        'status' => "0",
                        'message' => 'Otp does not match!.'
                    ), REST_Controller::HTTP_OK);
                }
            } else {
                $this->response(array(
                    'status' => "0",
                    'message' => 'Email id does not exist!.'
                ), REST_Controller::HTTP_OK);
            }
        }
    }

    public function addEditCart_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['deleted'] = $this->post('deleted');
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['product_id'] = $this->post('product_id');
            $userData['qty'] = $this->post('qty');
            $userData['discount'] = $this->post('discount');
            $userData['discount_type'] = $this->post('discount_type');
            $userData['comments'] = $this->post('comments');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('product_id', 'Prodcut Id', 'trim|required');
        $this->form_validation->set_rules('qty', 'qty', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('product_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('qty')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('qty'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $lang_id = $userData['language_id'];
            $prd_id = $userData['product_id'];

            $product = $this->Apimodel->get_cond('products', "product_id=" . $prd_id . "");

            if (!empty($product)) {
                $checkCart = $this->Apimodel->get_cond('cart', "product_id=" . $prd_id . " AND user_id=" . $userData['user_id'] . "");
                if (!empty($checkCart)) {
                    $updateData = array(
                        'deleted' => $userData['deleted'],
                        'qty' => $userData['qty'],
                        'discount' => @$userData['discount'],
                        'discount_type' => @$userData['discount_type'],
                        'comments' => $userData['comments'],
                    );

                    $where = "cart_id = $checkCart->cart_id";

                    $this->Apimodel->update_cond("cart", $where, $updateData);
                    $insert = $checkCart->cart_id;
                } else {
                    $mydata = array(
                        'language_id' => $userData['language_id'],
                        'product_id' => $userData['product_id'],
                        'user_id' => $userData['user_id'],
                        'qty' => $userData['qty'],
                        'discount' => @$userData['discount'],
                        'discount_type' => @$userData['discount_type'],
                        'comments' => @$userData['comments'],
                        'created' => date("Y-m-d H:i:s")
                    );
                    $insert = $this->Apimodel->add_details('cart', $mydata);
                }

                if ($insert) {
                    $this->response([
                        'status' => "1",
                        'message' => 'updated successfully.',
                        'cart_id' => strval($insert),
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function updateEditOrder_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['product_id'] = $this->post('product_id');
            $userData['delivered_pack'] = $this->post('delivered_pack');
            $userData['delivered_qty'] = $this->post('delivered_qty');
            $userData['pack'] = $this->post('pack');
            $userData['quantity'] = $this->post('quantity');
        }

        $this->form_validation->set_rules('product_id', 'Prodcut Id', 'trim|required');
        $this->form_validation->set_rules('delivered_qty', 'delivered_qty', 'trim|required');
        $this->form_validation->set_rules('delivered_pack', 'delivered_pack', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('product_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('delivered_qty')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('delivered_qty'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('delivered_pack')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('delivered_pack'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            //$lang_id = $userData['language_id'];
            $prd_id = $userData['product_id'];
            $orderId = $userData['order_id'];

            //$product = $this->Apimodel->get_cond('products', "product_id=".$prd_id."");

            //if(!empty($product))
            //{

            $dqty = -1;
            $dpck = -1;

            if ($userData['delivered_pack'] != 'X') {
                $dpck = $userData['delivered_pack'];
            }

            if ($userData['delivered_qty'] != 'X') {
                $dqty = $userData['delivered_qty'];
            }


            /*'delivered_quantity' =>$userData['delivered_qty'],
						'delivered_pack' =>$userData['delivered_pack']*/

            $updateData = array(
                'delivered_quantity' => $dqty,
                'delivered_pack' => $dpck,
                'pack' => $dpck,
            );

            $where = "order_id = $orderId AND product_id = $prd_id";

            $this->Apimodel->update_cond("order_details", $where, $updateData);
            $insert = $orderId;

            if ($insert) {
                $this->response([
                    'status' => "1",
                    'message' => 'updated successfully.',                    
                    'delivered_pack' => $dpck,
                    'delivered_quantity' => $dqty,
                    'Pack' => $userData['pack'],
                    'Quantity' => $userData['quantity'],    
                    'cart_id' => strval($insert)
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => "Some problems occurred, please try again."
                ], REST_Controller::HTTP_OK);
            }
            //}else{
            //$this->response([
            //		'status' => "0",
            //		'error' => 'No record was found.'
            //	], REST_Controller::HTTP_NOT_FOUND);
            //}
        }
    }


    protected function getOrderDetailDataFromRequest() {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['product_id'] = $this->post('product_id');
            $userData['sale_price'] = $this->post('sale_price');
            $userData['delivered_pack'] = $this->post('delivered_pack');
            $userData['qty'] = $this->post('qty');
            $userData['discount'] = $this->post('discount');
            $userData['discount_type'] = $this->post('discount_type');
            $userData['comments'] = $this->post('comments');
        }
        return $userData;
    }

    protected function validateOrderDetailRequest($userData) {
        $this->form_validation->set_rules('product_id', 'Prodcut Id', 'trim|required');
        $this->form_validation->set_rules('qty', 'qty', 'trim|required');

        $responseHTTPCODE = REST_Controller::HTTP_OK;
        $error="";
        $responseBody = [ 'status' => "0" ];

        if ($this->form_validation->run() === false) {

            if (form_error('product_id')) {
                $error.= strip_tags(form_error('product_id'));
            }

            if (form_error('qty')) {
                $error .= strip_tags(form_error('qty'));
            }

        }
        else {
            $prd_id = $userData['product_id'];
            $product = $this->Apimodel->get_cond('products', "product_id=" . $prd_id . "");
            if (empty($product)) {
                $responseHTTPCODE = REST_Controller::HTTP_NOT_FOUND;
                $error .= 'No record was found.';
            }
        }

        if(!empty($error)) {
            $responseBody['error'] = $error;
            return [
                'content'=>$responseBody,
                'http_code' => $responseHTTPCODE
            ];
        }
        return true;
    }



    /**
     * Adds or updates an order_detail for a given order (detecting if present in cart)
     * @return void
     */

 public function AddHistory_post() {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;

            $order_id = $userData['order_id'];
            $company_id = $userData['company_id'];
            $user_id = $userData['user_id'];
            $status = $userData['status'];

             $dataHistoryStatusOrder = [
                        "order_id" => $order_id,
                        "company_id" => $company_id,
                        "user_id" => $userId,
                        "status_id" => $status,
                    ];
                    $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);

            $this->response([
                'status' => '1',
                'message' => 'updated successfully.',
                "order_id" => $order_id,
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "cod_status" => $status
            ], REST_Controller::HTTP_OK);

        } else {
            $order_id = $this->post('order_id');
            $company_id = $this->post('company_id');
            $user_id = $this->post('user_id');
            $status = $this->post('status');

             $dataHistoryStatusOrder = [
                        "order_id" => $order_id,
                        "company_id" => $company_id,
                        "user_id" => $userId,
                        "status_id" => $status,
                    ];
                    $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);

            $this->response([
                'status' => '1',
                "order_id" => $order_id,
                        "company_id" => $company_id,
                        "user_id" => $user_id,
                        "status_id" => $status,
                'message' => 'updated successfully.'
            ], REST_Controller::HTTP_OK);
        }

       $this->response([
                'status' => '0',
                'message' => 'Complete Fields.'
            ], REST_Controller::HTTP_OK);
   
    }

    public function addEditOrder_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['product_id'] = $this->post('product_id');
            $userData['sale_price'] = $this->post('sale_price');
            $userData['delivered_pack'] = $this->post('delivered_pack');
            $userData['qty'] = $this->post('qty');
            $userData['discount'] = $this->post('discount');
            $userData['discount_type'] = $this->post('discount_type');
            $userData['comments'] = $this->post('comments');
        }

        $this->form_validation->set_rules('product_id', 'Prodcut Id', 'trim|required');
        $this->form_validation->set_rules('qty', 'qty', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('product_id')) {
                $this->response([
                                    'status' => "0",
                                    'error' => strip_tags(form_error('product_id'))
                                ], REST_Controller::HTTP_OK);
            }

            if (form_error('qty')) {
                $this->response([
                                    'status' => "1",
                                    'error' => strip_tags(form_error('qty'))
                                ], REST_Controller::HTTP_OK);
            }

        } else {

            $lang_id = $userData['language_id'];
            $prd_id = $userData['product_id'];

            $product = $this->Apimodel->get_cond('products', "product_id=" . $prd_id . "");
            

            if (!empty($product)) {
                $checkCart = $this->Apimodel->get_cond('order_details', "product_id=" . $prd_id . " AND order_id=" . $userData['order_id'] . "");
                if (!empty($checkCart)) {
                    $updateData = array(
                        'qty' => $userData['qty'],
                        'discount' => @$userData['discount'],
                        'delivered_pack' => $userData['delivered_pack'],
                        'discount_type' => @$userData['discount_type'],
                        'comments' => $userData['comments'],
                    );

                    $where = "order_id = $checkCart->order_id AND product_id = $prd_id";

                    $this->Apimodel->update_cond("order_details", $where, $updateData);
                    $insert = $checkCart->order_id;
                } else {
                    $mydata = array(
                        'order_id' => $userData['order_id'],
                        'product_id' => $userData['product_id'],
                        'sale_price' => $userData['sale_price'],
                        'fob_price' => $product->fob_price,
                        'purchase_price' => $product->purchase_price,
                        'delivered_pack' => $userData['delivered_pack'],
                        'qty' => $userData['qty'],
                        'discount' => @$userData['discount'],
                        'discount_type' => @$userData['discount_type'],
                        'comments' => @$userData['comments'],
                        'created' => date("Y-m-d H:i:s")
                    );
                    $insert = $this->Apimodel->add_details('order_details', $mydata);
                }

                if ($insert) {

                    $orderId = $userData['order_id'];
                    //$where3 = "order_id = $orderId  AND qty=0";
                    //$this->Apimodel->delete_single_con('order_details', $where3);

                    $this->response([
                                        'status' => "1",
                                        'message' => 'updated successfully.',
                                        'cart_id' => strval($insert),
                                        'product_id' => $userData['product_id'],
                                        'qty' => $userData['qty'],
                                    ], REST_Controller::HTTP_OK);
//                                    $this->updateOrderStatusHistory($orderId, 11);                    
                } else {
                    $this->response([
                                        'status' => "0",
                                        'error' => "Some problems occurred, please try again."
                                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                                    'status' => "0",
                                    'error' => 'No record was found.'
                                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function deleteCart_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['cart_id'] = $this->post('cart_id');
        }

        $this->form_validation->set_rules('cart_id', 'Cart Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('cart_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('cart_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $cart_id = $userData['cart_id'];

            $cart = $this->Apimodel->get_cond('cart', "cart_id=" . $cart_id . "");

            if (empty($cart)) {
                $this->response([
                    'status' => "0",
                    'error' => 'Invalid Cart Id'
                ], REST_Controller::HTTP_OK);
            } else {

                $deleted = "1";

                $updateData = array(
                    'deleted' => $deleted,
                );

                $where = "cart_id = $cart_id";

                $delete = $this->Apimodel->update_cond("cart", $where, $updateData);


                if ($delete) {

                    $this->response([
                        'status' => "1",
                        'message' => 'Cart removed successfully'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => 'Opps, Some thing went wrong'
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }


    public function deleteCartAll_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $user_id = $userData['user_id'];

            $cart = $this->Apimodel->get_cond_all('cart', "user_id=" . $user_id . "");

            if (empty($cart)) {
                $this->response([
                    'status' => "0",
                    'error' => 'Cart is Empty!'
                ], REST_Controller::HTTP_OK);
            } else {

                $where = array(
                    'user_id' => $user_id
                );

                $deleted = "1";

                $updateData = array(
                    'deleted' => $deleted,
                );

                $delete = $this->Apimodel->update_cond("cart", $where, $updateData);


                if ($delete) {

                    $this->response([
                        'status' => "1",
                        'message' => 'Cart removed successfully'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => 'Opps, Some thing went wrong'
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }


    public function clearCart_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);


        $userData['user_id'] = $this->post('user_id');

        $user_id = $userData['user_id'];

        $where = array(
            'user_id' => $user_id
        );

        $order_status = "-1";
        $updateData = array(
            'user_id' => $order_status,
        );

        $where = array(
            'user_id' => $user_id
        );

        $delete = $this->Apimodel->update_cond("orders", $where, $updateData);

        if ($delete) {

            $this->response([
                'status' => "1",
                'message' => 'Cart removed successfully'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => 'Opps, Some thing went wrong'
            ], REST_Controller::HTTP_OK);
        }


        // fin de la function
    }

    public function deleteCartAll1_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
        }

        $user_id = $userData['user_id'];

        $where = array(
            'user_id' => $user_id
        );

        $delete = $this->Apimodel->delete_single_con('cart', $where);

        if ($delete) {

            $this->response([
                'status' => "1",
                'message' => 'Cart removed successfully'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => 'Opps, Some thing went wrong'
            ], REST_Controller::HTTP_OK);
        }

    }

    public function cartList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $company_id = $userData['company_id'];

            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("cart", "user_id=" . $userData['user_id'] . " AND deleted='0'");

            if (!empty($list)) {
                foreach ($list as $cart) {
                    $productInfo = $this->Apimodel->get_cond('products', "product_id='$cart->product_id'");

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$productInfo->product_id");

                    $gallleryList = array();

                    $pedidosList = array();

                    $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$productInfo->product_id ");


                    foreach ($pedidos as $ped) {
                        $pedidosList[] = array(
                            'customer_id' => $ped->customer_id,
                            'qty' => $ped->qty,
                            'requested' => $ped->requested,
                        );
                    }


                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'company_id' => $company_id,
                            'product_id' => $productInfo->pro_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                        );
                    }

                    $totalQuantitySql = "SELECT SUM(odt.qty) AS total_quantity, od.* FROM `orders` as od INNER JOIN `order_details` as odt ON odt.order_id=od.order_id WHERE od.payment_status='1' AND odt.product_id='" . $productInfo->product_id . "' AND od.company_id='" . $company_id . "'";
                    $totalOrder = $this->Apimodel->fetch_single_join($totalQuantitySql);

                    if (!empty($totalOrder)) {
                        if ($totalOrder->total_quantity) {
                            $total_quantity = @$totalOrder->total_quantity;
                        } else {
                            $total_quantity = "0";
                        }
                    } else {
                        $total_quantity = "0";
                    }

                    $availableStock = ($productInfo->stock - $total_quantity);

                    $array[] = [
                        'cart_id' => $cart->cart_id,
                        'qty' => $cart->qty,
                        'discount' => $cart->discount,
                        'discount_type' => $cart->discount_type,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'comments' => $cart->comments,
                        'product_id' => $cart->product_id,
                        'name' => strtoupper($productInfo->name),
                        'minimum_stock' => $productInfo->minimum_stock,
                        'currency_type' => $this->getSymbol($productInfo->currency_type),
                        'sku' => $productInfo->sku,
                        'category_id' => $productInfo->category_id,
                        'sale_price' => $productInfo->sale_price,
                        'fob_price' => $productInfo->fob_price,
                        'purchase_price' => $productInfo->purchase_price,
                        'barcode' => $productInfo->barcode,
                        'force_moq' => $productInfo->notify_minimum_stock,
                        'tags' => $productInfo->tags,
                        'descriptions' => $productInfo->descriptions,
                        'unit_type' => $productInfo->unit_type,
                        'stock' => $productInfo->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ];
                }

                $array = $this->arrcheck($array);

                $profileInfo = $this->Apimodel->get_cond('user_profiles', "profile_id='" . $userInfo->profile . "'");

                $info = array(
                    'profile_id' => $profileInfo->profile_id,
                    'profile_name' => $profileInfo->profile_name,
                    'company_id' => $profileInfo->company_id,
                    'order_discount' => $profileInfo->order_discount,
                    'order_disc_price' => ($profileInfo->order_disc_price == '') ? '0' : $profileInfo->order_disc_price,
                    'order_net_discount' => $profileInfo->order_net_discount,
                    'order_net_disc_price' => ($profileInfo->order_net_disc_price == '') ? '0' : $profileInfo->order_net_disc_price,
                    'can_change_price' => $profileInfo->can_change_price,
                    'can_send_catalog' => $profileInfo->can_send_catalog,
                    'can_create_customer' => $profileInfo->can_create_customer,
                    'created' => $profileInfo->created,
                    'status' => $profileInfo->status,
                );


                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'permission_info' => $info,
                    'list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }

    }



    public function addOrder_post()
    {
        $userData = $this->getOrderDataFromRequest();
        $lang_id = $userData['language_id'];
        $sellerId = $userData['user_id'];
        $company_id = $userData['company_id'];


        if (($response=$this->validateOrderRequest($userData))===true)
         {
             $response = ['http_code' => REST_Controller::HTTP_OK];

             $customer = $this->Apimodel->get_cond('customers', "customer_id=" . $userData['customer_id']);
             if(!isset($sellerId))
                $sellerId = $this->getUserIdFromCustomerOrCompany($customer, $userData['company_id']);

             $newOrder = $this->OrdenModel->makeOrder(
                 $sellerId,
                 $customer->customer_id,
                 $company_id,
                 $userData['uuid'],
                 $this->OrdenModel::STANDARD_ORDER,
                 $userData['discount_type'],
                 $this->Commonmodel->get_value_format($userData['discount']),
                 $userData['order_comment'],
                 'app'
             );
             $newOrder->language_id = $lang_id;
             $newOrder->payment_status = 1;

             $itemList = $this->prepareOrderDetails($userData['itemList']);


             if($this->saveOrder($newOrder, $customer, $itemList))
             {
                 $this->saveOrderBackup($newOrder, $customer, $itemList);

                 $this->Apimodel->delete_single_con('cart', "user_id=" . $userData['user_id']);
                 $response['content'] =
                     [
                         'status' => "1",
                         'message' => "New order generated successfully.",
                         'sellerId' => $sellerId,
                         'companyId' => $company_id,
                         'order_id' => $newOrder->order_id,
                     ];
                  //$this->updateOrderStatusHistory($newOrder->order_id, 0);
                  //addHistory($newOrder->order_id,$company_id,$sellerId,0);
                     $dataHistoryStatusOrder = [
                        "order_id" => $newOrder->order_id,
                        "company_id" => $company_id,
                        "user_id" => $sellerId,
                        "status_id" => 0,
                    ];
                    $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);
             } else {
                 $response['content'] =
                     [
                         'status' => "0",
                         'error' => "Some problems occurred, please try again."
                     ];
             }
        }

        $this->response($response['content'], $response['http_code']);
    }

    public function addOrdererp_post()
    {
        $userData = $this->getOrderDataFromRequest();
        $userData['created'] = $this->post('created');

        $lang_id = $userData['language_id'];
        $warehouseUserId = $userData['user_id'];
        $company_id = $userData['company_id'];

        if (($response=$this->validateOrderRequest($userData))===true)
        {
            $response = ['http_code' => REST_Controller::HTTP_OK];

            $customer = $this->Apimodel->get_cond('customers', "customer_id=" . $userData['customer_id']);
            $userId = $this->getUserIdFromCustomerOrCompany($customer, $userData['company_id']);

            $newOrder = $this->OrdenModel->makeOrder(
                $userId,
                $customer->customer_id,
                $company_id,
                $userData['uuid'],
                $this->OrdenModel::STANDARD_ORDER,
                $userData['discount_type'],
                $customer->discount,
                $userData['order_comment'],
                'erp',
                OrdenModel::STATUS_PLACED,
                "",
                $userData['order_number']
            );
            $newOrder->created = $userData['created'];
            $newOrder->language_id = $lang_id;
            $newOrder->warehouse_user_id = $warehouseUserId;
            $newOrder->payment_status = 1;

            $itemList = $this->prepareOrderDetails($userData['itemList']);
            if($this->saveOrder($newOrder, $customer, $itemList))
            {
                $response['content'] =
                    [
                        'status' => "1",
                        'message' => "New order generated successfully.",
                        'order_id' => $newOrder->order_id,
                    ];

                    $dataHistoryStatusOrder = [
                        "order_id" => $newOrder->order_id,
                        "company_id" => $company_id,
                        "user_id" => $sellerId,
                        "cod_status" => 0,
                    ];
                    $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);
            } else {
                $response['content'] =
                    [
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ];
            }
        }

        $this->response($response['content'], $response['http_code']);

    }

    /**
     * Validates if userData is enough for the credit Note creation
     * @param $userData
     * @return bool|array ture in case of correct validation, an array with the response message and the http code otherwise
     */
    protected function validateOrderRequest($userData, $userIdIsWharehouseUser=false) {
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');
        $this->form_validation->set_rules('discount', 'Discount', 'trim|required');
        $this->form_validation->set_rules('discount_type', 'Discount type', 'trim|required');

        $responseHTTPCODE = REST_Controller::HTTP_OK;
        $error="";
        $responseBody = [ 'status' => "0" ];

        if ($this->form_validation->run() === false) {

            if (form_error('user_id')) {
                $error .= strip_tags(form_error('user_id'))." ";
            }
            if (form_error('company_id')) {
                $error .= strip_tags(form_error('company_id'))." ";
            }
            if (form_error('language_id')) {
                $error .= strip_tags(form_error('language_id'))." ";
            }
            if (form_error('customer_id')) {
                $error .= strip_tags(form_error('customer_id'))." ";
            }
            if (form_error('discount')) {
                $error .= strip_tags(form_error('discount'))." ";
            }
            if (form_error('discount_type')) {
                $error .= strip_tags(form_error('discount_type'))." ";
            }
        } else {
            $responseHTTPCODE=REST_Controller::HTTP_NOT_FOUND;
            $user_id = $userData['user_id'];

            if (empty($userData['itemList'])) {
                $responseHTTPCODE=REST_Controller::HTTP_BAD_REQUEST;
                $error = "itemList is required.";
            }

            if($userIdIsWharehouseUser) {
                $warehouseUser = $this->Apimodel->get_cond('warehouse_users', "user_id=" . $user_id);
                if (empty($warehouseUser)) {
                    $error .= 'No warehouse users with that Id was found.';
                }
            }
            else { // user is seller
                $seller = $this->Apimodel->get_cond('vendors', "user_id=" . $user_id);
                if (empty($seller)) {
                    $error .= 'No sellers with that user id was found.';
                }
            }

        }

        if(!empty($error))
        {
            $responseBody['error'] = $error;
            return [
                'content'=>$responseBody,
                'http_code' => $responseHTTPCODE
            ];

        }


        return true;
    }

    /**
     * Saves the order with its details calculating totals and saving history
     *
     * There is a very similar method on controllers/company/Order (this one is copied from there).
     * We are keeping them separated for the moment because of lack of correct hierarchy
     * @param object $order
     * @param object $customer
     * @param $orderDetails
     * @param string $ordersTable
     * @return bool|array
     */
    protected function saveOrder(object $order, object $customer, $orderDetails, $ordersTable='orders')
    {

        if ($this->mymodel->save($ordersTable, $order)) {

            $order->order_id = $this->mymodel->lastId();

            $this->OrdenModel->crudOrderDetails($order, $customer, $orderDetails,$ordersTable!='orders');
            $this->updateOrderStatusHistory($order->order_id, $order->order_status);

            return $this->mymodel->update($order, 'orders', ['order_id' => $order->order_id]);
        }
        return false;
    }

    protected function saveOrderBackup(object $order, object $customer, $orderDetails) {
        if($this->mymodel->table_exists('bakorders'))
        {
            $this->saveOrder($order, $customer, $orderDetails,'bakorders');
        }
    }

    /**
     * @param $order_id
     * @return void
     */
    protected function updateOrderStatusHistory($order_id, $status_id): void
    {
        $dataHistoryStatusOrder = [
            "order_id" => $order_id,
            "company_id" => $this->company_info->company_id,
            "user_id" => $this->sub_user->company_id,
            "status_id" => $status_id,
        ];
        $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);
    }

    /**
     * Gets the user data from input or post variables
     * @return array
     */
    protected function getOrderDataFromRequest() {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['language_id'] = $this->post('language_id');
            $userData['customer_id'] = $this->post('customer_id');
            $userData['order_comment'] = $this->post('order_comment');
            $userData['itemList'] = $this->post('itemList');
            $userData['discount'] = $this->post('discount');
            $userData['discount_type'] = $this->post('discount_type');
            $userData['company_id'] = $this->post('company_id');
            $userData['tipo_d'] = $this->post('tipo_d');
            $userData['order_status'] = $this->post('order_status');
            $userData['groupcustomer'] = $this->post('groupcustomer');
            $userData['tipolista'] = $this->post('tipolista');
            $userData['perc_price'] = $this->post('perc_price');
            $userData['salesp'] = $this->post('salesp');
            $userData['totalprice'] = $this->post('totalprice');
            $userData['uuid'] = $this->post('uuid');
        }
        return $userData;
    }

    protected function prepareOrderDetail(array $orderDetail) {
        $orderDetail['comments'] = $orderDetail['comment'];
        $orderDetail['id_producto'] = $orderDetail['product_id'];
        $orderDetail['sale_price'] = $orderDetail['salesp'];
        return $orderDetail;
    }

    protected function prepareCreditNoteOrderDetails(array $itemList) {

        foreach ($itemList as $ind => $val) {
            //Make quantityNegative
            $itemList[$ind]['qty'] = $itemList[$ind]['qty'] *-1;
            // workarround to fix the existing difference between comment name on app with the column name
            $itemList[$ind] = $this->prepareOrderDetail($itemList[$ind]);
        }

        return $itemList;

    }

    protected function prepareOrderDetails(array $itemList) {

        foreach ($itemList as $ind => $val) {
            // workarround to fix the existing difference between comment name on app with the column name
            $itemList[$ind] = $this->prepareOrderDetail($itemList[$ind]);
        }
        return $itemList;

    }

    public function addCredit_post()
    {
        $userData = $this->getOrderDataFromRequest();

        $lang_id = $userData['language_id'];
        $warehouseUserId = $userData['user_id'];
        $company_id = $userData['company_id'];

        if (($response=$this->validateOrderRequest($userData,true))===true) {
            $response = ['http_code' => REST_Controller::HTTP_OK];

            $customer = $this->Apimodel->get_cond('customers', "customer_id=" . $userData['customer_id']);
            $userId = $this->getUserIdFromCustomerOrCompany($customer, $userData['company_id']);

            $creditNote = $this->OrdenModel->makeOrder(
                $userId,
                $customer->customer_id,
                $company_id,
                $userData['uuid'],
                $this->OrdenModel::CREDIT_ORDER,
                $userData['discount_type'],
                $this->Commonmodel->get_value_format($userData['discount']),
                $userData['order_comment'],
                'app'
            );
            $creditNote->language_id = $lang_id;
            $creditNote->warehouse_user_id = $warehouseUserId;
            $creditNote->payment_status = 1;

            $itemList = $this->prepareCreditNoteOrderDetails($userData['itemList']);

            if($this->saveOrder($creditNote, $customer, $itemList))
            {

                $this->saveOrderBackup($creditNote, $customer, $itemList);

                $this->Apimodel->delete_single_con('cart', "user_id=" . $userData['user_id']);
                $response['content'] =
                    [
                        'status' => "1",
                        'message' => "New order generated successfully. " . $userId . " " . $customer->user_id,
                        'order_id' => $creditNote->order_id,
                    ];
                    //$this->updateOrderStatusHistory($creditNote->order_id, 0);
                    $dataHistoryStatusOrder = [
                        "order_id" => $creditNote->order_id,
                        "company_id" => $company_id,
                        "user_id" => $sellerId,
                        "status_id" => 0,
                    ];
                    $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);
                    //$insert1 = $this->('order_status_history', $dataHistoryStatusOrder);
            } else {
                $response['content'] =
                    [
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ];
            }
        }

        $this->response($response['content'], $response['http_code']);
    }




    public function orderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['customer_id'] = $this->post('customer_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }


            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }

        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];

            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("warehouseorders", "customer_id=" . $userData['customer_id'] . " AND tipo_d='D' ORDER BY created DESC");

            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => number_format($pr->qty, 2),
                            'sale_price' => number_format($pr->sale_price, 2),
                            'fob_price' => number_format($pr->fob_price, 2),
                            'purchase_price' => number_format($pr->purchase_price, 2),
                            'barcode' => $productInfo->barcode,
                            'discount' => number_format($pr->discount, 2),
                            'discount_type' => $pr->discount_type,
                            'discount' => number_format($pr->discount, 2),
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                        ];
                    }

                    $array[] = [
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal - $cart->discount, 2),
                        'discount' => number_format($cart->discount, 2),
                        'discount_a' => number_format($cart->discount_a, 2),
                        'amount' => number_format($subTotal, 2),
                        'total_amount' => number_format($subTotal - $cart->discount, 2),
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function worderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['customer_id'] = $this->post('customer_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }


            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }

        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];

            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("warehouseorders", "customer_id=" . $userData['customer_id'] . " AND tipo_d='D' AND created >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)  AND created <= CURDATE() ORDER BY created DESC");

            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => number_format($pr->qty, 2),
                            'sale_price' => number_format($pr->sale_price, 2),
                            'fob_price' => number_format($pr->fob_price, 2),
                            'purchase_price' => number_format($pr->purchase_price, 2),
                            'barcode' => $productInfo->barcode,
                            'discount' => number_format($pr->discount, 2),
                            'discount_type' => $pr->discount_type,
                            'discount' => number_format($pr->discount, 2),
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                        ];
                    }

                    $array[] = [
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal - $cart->discount, 2),
                        'discount' => number_format($cart->discount, 2),
                        'discount_a' => number_format($cart->discount_a, 2),
                        'amount' => number_format($subTotal, 2),
                        'total_amount' => number_format($subTotal - $cart->discount, 2),
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function bakorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['customer_id'] = $this->post('customer_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }


            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }

        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];

            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("bakwarehouseorders", "customer_id=" . $userData['customer_id'] . " AND tipo_d='D' ORDER BY created DESC");

            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('bakorder_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => number_format($pr->qty, 2),
                            'sale_price' => number_format($pr->sale_price, 2),
                            'fob_price' => number_format($pr->fob_price, 2),
                            'purchase_price' => number_format($pr->purchase_price, 2),
                            'barcode' => $productInfo->barcode,
                            'discount' => number_format($pr->discount, 2),
                            'discount_type' => $pr->discount_type,
                            'discount' => number_format($pr->discount, 2),
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                        ];
                    }

                    $array[] = [
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal - $cart->discount, 2),
                        'discount' => number_format($cart->discount, 2),
                        'discount_a' => number_format($cart->discount_a, 2),
                        'amount' => number_format($subTotal, 2),
                        'total_amount' => number_format($subTotal - $cart->discount, 2),
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function softorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
            //$userData['year_id'] = $this->post('year_id');
            //$userData['month_id'] = $this->post('month_id');
        }

        //$this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        //$this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            /*if(form_error('language_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('language_id'))
				], REST_Controller::HTTP_OK);
			}

			if(form_error('user_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('user_id'))
				], REST_Controller::HTTP_OK);
			}*/
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $year_id = $userData['year_id'];
            $month_id = $userData['month_id'];


            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=" . $userData['company_id'] . " AND (tipo_d='D' or tipo_d='C') AND updated>=DATE_SUB(CURDATE(), INTERVAL 2 DAY) ORDER BY updated DESC");

            //$list = "SELECT * FROM warehouseorders WHERE company_id = ".$userData['company_id']." AND tipo_d='D' AND ano_id=". $userData['year_id'];


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        if ($cart->order_status == 11) {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->delivered_quantity,
                                'pack' => $pr->delivered_pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];

                        } else {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->qty,
                                'pack' => $pr->pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];
                        }


                    }

                    $array[] = [
                        'user_id' => $cart->user_id,
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'order_status' => $cart->order_status,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

 public function osoftorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
        	$userData['order_id'] = $this->post('order_id');
        }

        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $year_id = $userData['year_id'];
            $month_id = $userData['month_id'];


            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=" . $userData['company_id'] . " AND order_id=". $userData['order_id']);


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        if ($cart->order_status == 11) {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->delivered_quantity,
                                'pack' => $pr->delivered_pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];

                        } else {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->qty,
                                'pack' => $pr->pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];
                        }


                    }

                    $array[] = [
                        'user_id' => $cart->user_id,
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'order_status' => $cart->order_status,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function wwsoftorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
            //$userData['year_id'] = $this->post('year_id');
            //$userData['month_id'] = $this->post('month_id');
        }

        //$this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        //$this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            /*if(form_error('language_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('language_id'))
				], REST_Controller::HTTP_OK);
			}

			if(form_error('user_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('user_id'))
				], REST_Controller::HTTP_OK);
			}*/
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $year_id = $userData['year_id'];
            $month_id = $userData['month_id'];


            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=" . $userData['company_id'] . " AND (tipo_d='D' or tipo_d='C') AND updated>=DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY updated DESC");

            //$list = "SELECT * FROM warehouseorders WHERE company_id = ".$userData['company_id']." AND tipo_d='D' AND ano_id=". $userData['year_id'];


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        if ($cart->order_status == 11) {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->delivered_quantity,
                                'pack' => $pr->delivered_pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];

                        } else {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->qty,
                                'pack' => $pr->pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];
                        }


                    }

                    $array[] = [
                        'user_id' => $cart->user_id,
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'order_status' => $cart->order_status,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function baksoftorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
            //$userData['year_id'] = $this->post('year_id');
            //$userData['month_id'] = $this->post('month_id');
        }

        //$this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        //$this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            /*if(form_error('language_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('language_id'))
				], REST_Controller::HTTP_OK);
			}

			if(form_error('user_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('user_id'))
				], REST_Controller::HTTP_OK);
			}*/
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $year_id = $userData['year_id'];
            $month_id = $userData['month_id'];


            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("bakwarehouseorders", "company_id=" . $userData['company_id'] . " AND (tipo_d='D' or tipo_d='C') AND created>=DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY created DESC");

            //$list = "SELECT * FROM warehouseorders WHERE company_id = ".$userData['company_id']." AND tipo_d='D' AND ano_id=". $userData['year_id'];


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('bakorder_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        if ($cart->order_status == 11) {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->delivered_quantity,
                                'pack' => $pr->delivered_pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];

                        } else {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->qty,
                                'pack' => $pr->pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];
                        }


                    }

                    $array[] = [
                        'user_id' => $cart->user_id,
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'order_status' => $cart->order_status,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function myoftorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
            //$userData['year_id'] = $this->post('year_id');
            //$userData['month_id'] = $this->post('month_id');
        }

        //$this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        //$this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            /*if(form_error('language_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('language_id'))
				], REST_Controller::HTTP_OK);
			}

			if(form_error('user_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('user_id'))
				], REST_Controller::HTTP_OK);
			}*/
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $year_id = $userData['year_id'];
            $month_id = $userData['month_id'];


            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=" . $userData['company_id'] . " AND (tipo_d='D' or tipo_d='C') AND created>=DATE_SUB(CURDATE(), INTERVAL 1 MONTH) ORDER BY created DESC");

            //$list = "SELECT * FROM warehouseorders WHERE company_id = ".$userData['company_id']." AND tipo_d='D' AND ano_id=". $userData['year_id'];


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        if ($cart->order_status == 11) {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->delivered_quantity,
                                'pack' => $pr->delivered_pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];

                        } else {
                            $newarray[] = [
                                'order_id' => $cart->order_id,
                                'currency_type' => $this->getSymbol($productInfo->currency_type),
                                'product_id' => $pr->product_id,
                                'name' => strtoupper($productInfo->name),
                                'sku' => $productInfo->sku,
                                'qty' => $pr->qty,
                                'pack' => $pr->pack,
                                'delivered_pack' => $pr->delivered_pack,
                                'requested_qty' => $pr->qty,
                                'delivered_qty' => $pr->delivered_quantity,
                                'sale_price' => $pr->sale_price,
                                'fob_price' => $pr->fob_price,
                                'purchase_price' => $pr->purchase_price,
                                'amount_sales' => $pr->amount_sales,
                                'discount_amount' => $pr->discount_amount,
                                'total_amount' => $pr->total_amount,
                                'amount_delivered' => $pr->amount_delivered,
                                'discount_delivered' => $pr->discount_delivered,
                                'total_delivered' => $pr->total_delivered,
                                'barcode' => $productInfo->barcode,
                                'discount' => $pr->discount_delivered,
                                'discount_type' => $pr->discount_type,
                                'comment' => $pr->comment,
                                'created' => $pr->created,
                            ];
                        }


                    }

                    $array[] = [
                        'user_id' => $cart->user_id,
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'order_status' => $cart->order_status,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function softorderList2_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['year_id'] = $this->post('year_id');
            $userData['month_id'] = $this->post('month_id');
        }

        //$this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        //$this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            /*if(form_error('language_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('language_id'))
				], REST_Controller::HTTP_OK);
			}

			if(form_error('user_id'))
			{
				$this->response([
					'status' => "0",
					'error' => strip_tags(form_error('user_id'))
				], REST_Controller::HTTP_OK);
			}*/
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];
            $year_id = $userData['year_id'];
            $month_id = $userData['month_id'];


            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            //$list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=".$userData['company_id']." AND tipo_d='D' AND year(created)=".$year_id." AND month(created) = ".$month_id." ORDER BY created DESC");

            $consulta = "SELECT * FROM warehouseorders WHERE company_id = " . $userData['company_id'] . " AND tipo_d='D' AND ano_id=" . $year_id . " AND mes_id = " . $month_id . " ORDER BY created DESC";
            $list = $this->Apimodel->fetch_all_join($consulta);


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                        ];
                    }

                    $array[] = [
                        'user_id' => $cart->user_id,
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'warehouse_user_id' => $cart->warehouse_user_id,
                        'warehouse_assign_date' => $cart->warehouse_assign_date,
                        'warehouse_name' => $cart->warehouse_name,
                        'order_status' => $cart->order_status,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    //'year'=>$year_id,
                    //'month'=>$month_id,
                    //'consulta'=>$consulta,
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function syncorderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
        }
        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $user_id = $userData['user_id'];

            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            $list = $this->Apimodel->get_cond_all("orders", "company_id=" . $userData['company_id'] . " ");

            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $subTotal = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                        ];
                    }

                    $array[] = [
                        'tipo_d' => $cart->tipo_d,
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'user_id' => $cart->user_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function orderDetails_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
        }

        $company_id = $userData['company_id'];

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {

            $orderInfo = $this->Apimodel->get_cond("orders", "order_id=" . $userData['order_id'] . "");

            if (!empty($orderInfo)) {

                $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$orderInfo->order_id'");

                $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$orderInfo->customer_id'");

                $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$orderInfo->user_id'");

                $subTotal = 0;

                $newarray = array();

                foreach ($productList as $key => $pr) {
                    $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");

                    $gallleryList = array();

                    $pedidosList = array();

                    $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                    foreach ($pedidos as $ped) {
                        $pedidosList[] = array(
                            'customer_id' => $ped->customer_id,
                            'qty' => $ped->qty,
                            'requested' => $ped->requested,
                        );
                    }


                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'product_id' => $pr->pro_id,
                            'company_id' => $pr->company_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                        );
                    }

                    $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                    $subTotal = $totalprice + $subTotal;

                    $newarray[] = [
                        'currency_type' => $this->getSymbol($productInfo->currency_type),
                        'product_id' => $pr->product_id,
                        'name' => strtoupper($productInfo->name),
                        'sku' => $productInfo->sku,
                        'qty' => $pr->qty,
                        'pack' => $pr->pack,
                        'sale_price' => $pr->sale_price,
                        'fob_price' => $pr->fob_price,
                        'purchase_price' => $pr->purchase_price,
                        'barcode' => $productInfo->barcode,
                        'discount' => $pr->discount,
                        'discount_type' => $pr->discount_type,
                        'discount' => $pr->discount,
                        'comment' => $pr->comment,
                        'created' => $pr->created,
                        'images' => $gallleryList,
                        'requested' => $pedidosList
                    ];
                }

                $adiscount = 0;
                $totalamount = 0;

                if ($customerInfo->discount > 0) {
                    $adiscount = $subTotal * ($customerInfo->discount / 100);
                }

                $totalamount = $subTotal - $adiscount;

                $array = [
                    'company_id' => $orderInfo->order_id,
                    'order_id' => $orderInfo->order_id,
                    'order_number' => $orderInfo->order_number,
                    'order_comments' => $orderInfo->order_comments,
                    'amount' => number_format($subTotal, 2),
                    'ordered_total' => number_format($totalamount, 2),
                    'adiscount' => number_format($adiscount, 2),
                    'discount_a' => number_format($adiscount, 2),
                    'totalamount' => number_format($totalamount, 2),
                    'total_amount' => number_format($totalamount, 2),
                    'seller' => $vendorInfo->first_name . " " . $vendorInfo->last_name,
                    'discount' => $orderInfo->discount,
                    'discount_type' => $orderInfo->discount_type,
                    'business_name' => $customerInfo->business_name,
                    'customer_id' => $customerInfo->customer_id,
                    'name' => $customerInfo->name,
                    'email' => $customerInfo->email,
                    'phone' => $customerInfo->phone,
                    'commercial_address' => $customerInfo->commercial_address,
                    'commercial_delivery_address' => $customerInfo->commercial_delivery_address,
                    'commercial_country' => $customerInfo->commercial_country,
                    'commercial_state' => $customerInfo->commercial_state,
                    'commercial_city' => $customerInfo->commercial_city,
                    'commercial_zone' => $customerInfo->commercial_zone,
                    'commercial_zip_code' => $customerInfo->commercial_zip_code,
                    'dispatch_address' => $customerInfo->dispatch_address,
                    'created' => $orderInfo->created,
                    'updated' => $orderInfo->updated,
                    'product_list' => $newarray
                ];

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'order_info' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Data found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function warehouseOrderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['warehouse_user_id'] = $this->post('warehouse_user_id');
        }

        $language_id = $userData['language_id'];
        $company_id = $userData['company_id'];
        $user_id = $userData['warehouse_user_id'];

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('warehouse_user_id', 'Warehouse User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }
            if (form_error('warehouse_user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('warehouse_user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $user_id = $userData['warehouse_user_id'];

            $list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=" . $userData['company_id'] . " AND order_status!=9 AND warehouse_user_id ='" . $user_id . "' AND tipo_d='D' AND (order_status!=0 OR order_status!=9)  ORDER BY updated DESC");


            if (!empty($list)) {
                foreach ($list as $cart) {
                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $vendorInfo = $this->Apimodel->get_cond('warehouse_users', "user_id='$cart->user_id'");


                    $subTotal = 0;
                    $total_quantity = 0;
                    $total_dev_quantity = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");


                        $gallleryList = array();

                        $pedidosList = array();

                        $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                        foreach ($pedidos as $ped) {
                            $pedidosList[] = array(
                                'customer_id' => $ped->customer_id,
                                'qty' => $ped->qty,
                                'requested' => $ped->requested,
                            );
                        }


                        foreach ($imgList as $img) {
                            if ($img->images != "") {
                                $product_pic = base_url() . 'uploads/products/' . $userData['company_id'] . '/' . $img->images;
                            } else {
                                $product_pic = base_url() . 'images/noimg.png';
                            }
                            $gallleryList[] = array(
                                'product_id' => $pr->pro_id,
                                'company_id' => $pr->company_id,
                                'img_id' => $img->img_id,
                                'pic' => $product_pic,
                                'local' => $img->img_id . ".jpg",
                            );
                        }


                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $total_quantity = $total_quantity + @$pr->qty;

                        $totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE od.order_id='" . $cart->order_id . "' AND od.company_id='" . $company_id . "'";
                        $totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                        if (!empty($totalDevOrder)) {
                            if ($totalDevOrder->total_quantity) {
                                $total_dev_quantity = @$totalDevOrder->total_quantity;
                            } else {
                                $total_dev_quantity = "0";
                            }
                        } else {
                            $total_dev_quantity = "0";
                        }

                        $total_available_dev_quantity = $total_quantity - $total_dev_quantity;


                        $totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  odt.product_id='" . $pr->product_id . "' AND od.company_id='" . $company_id . "' AND od.`order_id`='" . $cart->order_id . "'";
                        $totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

                        if (!empty($totalDeliveredOrder)) {
                            if ($totalDeliveredOrder->total_delivered_quantity) {
                                $total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
                            } else {
                                $total_delivered_quantity = "0";
                            }
                        } else {
                            $total_delivered_quantity = "0";
                        }

                        $availableStock = ($productInfo->stock - $total_delivered_quantity);

                        $total_available_delivered_quantity = $pr->qty - $total_delivered_quantity;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'pack' => $pr->pack,
                            'stock' => $productInfo->stock,
                            'total_order' => $total_quantity,
                            'available_stock' => $availableStock,
                            'delivered_quantity' => $total_delivered_quantity,
                            'available_delivered_quantity' => $total_available_delivered_quantity,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                            'images' => $gallleryList,
                            'requested' => $pedidosList,
                        ];
                    }

                    $adiscount = 0;
                    $totalamount = 0;

                    if ($customerInfo->discount > 0) {
                        $adiscount = $subTotal * ($customerInfo->discount / 100);
                    }

                    $totalamount = $subTotal - $cart->discount;

                    $array[] = [
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'adiscount' => number_format($cart->discount, 2),
                        'totalamount' => number_format($totalamount, 2),
                        'total_quantity' => (string)$total_quantity,
                        'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'seller' => $vendorInfo->first_name . " " . $vendorInfo->last_name,
                        'payment_method' => $cart->payment_method,
                        'payment_status' => $cart->payment_status,
                        'transaction_id' => $cart->transaction_id,
                        'order_status' => $cart->order_status,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'company_id' => $cart->company_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function warehouseOrderInventoryList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['warehouse_user_id'] = $this->post('warehouse_user_id');
        }

        $language_id = $userData['language_id'];
        $company_id = $userData['company_id'];
        $user_id = $userData['warehouse_user_id'];

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('warehouse_user_id', 'Warehouse User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }
            if (form_error('warehouse_user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('warehouse_user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $user_id = $userData['warehouse_user_id'];

            //$list = $this->Apimodel->get_cond_all("orders", "company_id=".$userData['company_id']." AND order_status=2 AND tipo_d='I' AND warehouse_user_id=".$user_id."  ORDER BY updated DESC");
            $list = $this->Apimodel->fetch_all_join("SELECT * FROM inventory_orders WHERE company_id=" . $userData['company_id'] . " AND warehouse_user_id=" . $userData['warehouse_user_id'] . " AND (order_status=2 OR order_status=16) ORDER BY updated DESC");

            if (!empty($list)) {
                foreach ($list as $cart) {
                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$cart->user_id'");


                    $subTotal = 0;
                    $total_quantity = 0;
                    $total_dev_quantity = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        //$productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        //$imgList=$this->Apimodel->get_cond_all('product_images',"product_id=$pr->product_id");


                        //$gallleryList =array();

                        //$pedidosList =array();

                        //$pedidos = $this->Apimodel->get_cond_all('rs_requested',"product_id=$pr->product_id ");


                        /*foreach ($pedidos as $ped)
						{
							$pedidosList[]= array(
								'customer_id'=>$ped->customer_id,
								'qty'=>$ped->qty,
								'requested'=>$ped->requested,
							);
						}*/


                        /*	foreach ($imgList as $img)
						{
							if($img->images!="")
							{
								$product_pic = base_url().'uploads/products/'.$userData['company_id'].'/'.$img->images;
							} else {
								$product_pic = base_url().'images/noimg.png';
							}
							$gallleryList[]= array(
							'product_id'=>$pr->pro_id,
							'company_id'=>$pr->company_id,
							'img_id'=>$img->img_id,
							'pic'=>$product_pic,
							'local'=>$img->img_id.".jpg",
							);
						}*/


                        //$totalprice= ((@$pr->qty)*(@$pr->sale_price));
                        //$subTotal= $totalprice + $subTotal;

                        $total_quantity = $total_quantity + @$pr->qty;

                        //$totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE od.order_id='".$cart->order_id."' AND od.company_id='".$company_id."'";
                        //$totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                        /*if(!empty($totalDevOrder))
						{
							if($totalDevOrder->total_quantity) {
								$total_dev_quantity = @$totalDevOrder->total_quantity;
							} else {
								$total_dev_quantity = "0";
							}
						} else {
							$total_dev_quantity = "0";
						}

						$total_available_dev_quantity = $total_quantity-$total_dev_quantity;*/


                        /*$totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  odt.product_id='".$pr->product_id."' AND od.company_id='".$company_id."' AND od.`order_id`='".$cart->order_id."'";
						$totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

						if(!empty($totalDeliveredOrder)) {
							if($totalDeliveredOrder->total_delivered_quantity) {
								$total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
							} else {
								$total_delivered_quantity = "0";
							}
						} else {
							$total_delivered_quantity = "0";
						}*/

                        //$availableStock = ($productInfo->stock-$total_delivered_quantity);

                        //$total_available_delivered_quantity = $pr->qty-$total_delivered_quantity;

                        /*$newarray[] = [
							'currency_type'=>$this->getSymbol($productInfo->currency_type),
							'product_id' =>$pr->product_id,
							'name'=>$productInfo->name,
							'sku'=>$productInfo->sku,
							'qty'=>$pr->qty,
							'pack'=>$pr->pack,
							'stock'=>$productInfo->stock,
							'total_order'=>$total_quantity,
							'available_stock'=>$availableStock,
							'delivered_quantity'=>$total_delivered_quantity,
							'available_delivered_quantity'=>$total_available_delivered_quantity,
							'sale_price'=>$pr->sale_price,
							'fob_price'=>$pr->fob_price,
							'purchase_price'=>$pr->purchase_price,
							'barcode'=>$productInfo->barcode,
							'discount'=>$pr->discount,
							'discount_type'=>$pr->discount_type,
							'discount'=>$pr->discount,
							'comment'=>$pr->comment,
							'created'=>$pr->created,
							'images'=>$gallleryList,
							'requested'=>$pedidosList,
						];						*/
                    }

                    $adiscount = 0;
                    $totalamount = 0;

                    if ($cart->discount > 0) {
                        $adiscount = $subTotal * ($cart->discount / 100);
                    }

                    $totalamount = $subTotal - $adiscount;

                    $array[] = [
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'adiscount' => number_format($adiscount, 2),
                        'totalamount' => number_format($totalamount, 2),
                        'total_quantity' => (string)$total_quantity,
                        'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                        'discount' => $cart->discount,
                        'seller' => $vendorInfo->first_name . " " . $vendorInfo->last_name,
                        'payment_method' => $cart->payment_method,
                        'payment_status' => $cart->payment_status,
                        'transaction_id' => $cart->transaction_id,
                        'order_status' => $cart->order_status,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $cart->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $cart->name,
                        'email' => $cart->name,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'company_id' => $cart->company_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function warehouseDetailsOrderInventoryList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
        }

        $order_id = $userData['order_id'];

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_id'))
                ], REST_Controller::HTTP_OK);
            }


        } else {
            $order_id = $userData['order_id'];

            //$list = $this->Apimodel->get_cond_all("orders", "company_id=".$userData['company_id']." AND order_status=2 AND tipo_d='I' AND warehouse_user_id=".$user_id."  ORDER BY updated DESC");
            $list = $this->Apimodel->fetch_all_join("SELECT * FROM inventory_orders WHERE order_id=" . $userData['order_id'] . " ORDER BY updated DESC");

            if (!empty($list)) {
                foreach ($list as $cart) {
                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$cart->user_id'");


                    $subTotal = 0;
                    $total_quantity = 0;
                    $total_dev_quantity = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");


                        $gallleryList = array();

                        $pedidosList = array();

                        $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                        foreach ($pedidos as $ped) {
                            $pedidosList[] = array(
                                'customer_id' => $ped->customer_id,
                                'qty' => $ped->qty,
                                'requested' => $ped->requested,
                            );
                        }


                        foreach ($imgList as $img) {
                            if ($img->images != "") {
                                $product_pic = base_url() . 'uploads/products/' . $cart->company_id . '/' . $img->images;
                            } else {
                                $product_pic = base_url() . 'images/noimg.png';
                            }
                            $gallleryList[] = array(
                                'product_id' => $pr->pro_id,
                                'company_id' => $pr->company_id,
                                'img_id' => $img->img_id,
                                'pic' => $product_pic,
                                'local' => $img->img_id . ".jpg",
                            );
                        }


                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $total_quantity = $total_quantity + @$pr->qty;

                        $totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE od.order_id='" . $cart->order_id . "' AND od.company_id='" . $company_id . "'";
                        $totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                        if (!empty($totalDevOrder)) {
                            if ($totalDevOrder->total_quantity) {
                                $total_dev_quantity = @$totalDevOrder->total_quantity;
                            } else {
                                $total_dev_quantity = "0";
                            }
                        } else {
                            $total_dev_quantity = "0";
                        }

                        $total_available_dev_quantity = $total_quantity - $total_dev_quantity;


                        $totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  odt.product_id='" . $pr->product_id . "' AND od.company_id='" . $company_id . "' AND od.`order_id`='" . $cart->order_id . "'";
                        $totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

                        if (!empty($totalDeliveredOrder)) {
                            if ($totalDeliveredOrder->total_delivered_quantity) {
                                $total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
                            } else {
                                $total_delivered_quantity = "0";
                            }
                        } else {
                            $total_delivered_quantity = "0";
                        }

                        $availableStock = ($productInfo->stock - $total_delivered_quantity);

                        $total_available_delivered_quantity = $pr->qty - $total_delivered_quantity;

                        $newarray[] = [
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'pack' => $pr->pack,
                            'stock' => $productInfo->stock,
                            'total_order' => $total_quantity,
                            'available_stock' => $availableStock,
                            'delivered_quantity' => $total_delivered_quantity,
                            'available_delivered_quantity' => $total_available_delivered_quantity,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                            'images' => $gallleryList,
                            'requested' => $pedidosList,
                        ];
                    }

                    $adiscount = 0;
                    $totalamount = 0;

                    if ($cart->discount > 0) {
                        $adiscount = $subTotal * ($cart->discount / 100);
                    }

                    $totalamount = $subTotal - $adiscount;

                    $array[] = [
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'adiscount' => number_format($adiscount, 2),
                        'totalamount' => number_format($totalamount, 2),
                        'total_quantity' => (string)$total_quantity,
                        'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                        'discount' => $cart->discount,
                        'seller' => $vendorInfo->first_name . " " . $vendorInfo->last_name,
                        'payment_method' => $cart->payment_method,
                        'payment_status' => $cart->payment_status,
                        'transaction_id' => $cart->transaction_id,
                        'order_status' => $cart->order_status,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $cart->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $cart->name,
                        'email' => $cart->name,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'company_id' => $cart->company_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function warehouseCOrderList_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['warehouse_user_id'] = $this->post('warehouse_user_id');
        }

        $language_id = $userData['language_id'];
        $company_id = $userData['company_id'];
        $user_id = $userData['warehouse_user_id'];

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('warehouse_user_id', 'Warehouse User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }
            if (form_error('warehouse_user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('warehouse_user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $user_id = $userData['warehouse_user_id'];

            $list = $this->Apimodel->get_cond_all("warehouseorders", "company_id=" . $userData['company_id'] . " AND order_status IN (5,6,14) AND warehouse_user_id ='" . $user_id . "' AND tipo_d='D' ORDER BY updated DESC");


            if (!empty($list)) {
                foreach ($list as $cart) {


                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$cart->user_id'");

                    $subTotal = 0;
                    $total_quantity = 0;
                    $total_dev_quantity = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");
                        $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");


                        $gallleryList = array();

                        $pedidosList = array();

                        $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                        foreach ($pedidos as $ped) {
                            $pedidosList[] = array(
                                'customer_id' => $ped->customer_id,
                                'qty' => $ped->qty,
                                'requested' => $ped->requested,
                            );
                        }


                        foreach ($imgList as $img) {
                            if ($img->images != "") {
                                $product_pic = base_url() . 'uploads/products/' . $userData['company_id'] . '/' . $img->images;
                            } else {
                                $product_pic = base_url() . 'images/noimg.png';
                            }
                            $gallleryList[] = array(
                                'iproduct_id' => $pr->pro_id,
                                'company_id' => $pr->company_id,
                                'img_id' => $img->img_id,
                                'pic' => $product_pic,
                                'local' => $img->img_id . ".jpg",
                            );
                        }

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $total_quantity = $total_quantity + @$pr->qty;

                        $totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE od.order_id='" . $cart->order_id . "' AND od.company_id='" . $company_id . "'";
                        $totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                        if (!empty($totalDevOrder)) {
                            if ($totalDevOrder->total_quantity) {
                                $total_dev_quantity = @$totalDevOrder->total_quantity;
                            } else {
                                $total_dev_quantity = "0";
                            }
                        } else {
                            $total_dev_quantity = "0";
                        }

                        $total_available_dev_quantity = $total_quantity - $total_dev_quantity;


                        $totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  odt.product_id='" . $pr->product_id . "' AND od.company_id='" . $company_id . "' AND od.`order_id`='" . $cart->order_id . "'";
                        $totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

                        if (!empty($totalDeliveredOrder)) {
                            if ($totalDeliveredOrder->total_delivered_quantity) {
                                $total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
                            } else {
                                $total_delivered_quantity = "0";
                            }
                        } else {
                            $total_delivered_quantity = "0";
                        }

                        $availableStock = ($productInfo->stock - $total_delivered_quantity);

                        $total_available_delivered_quantity = $pr->qty - $total_delivered_quantity;

                        $newarray[] = [
                            'order_id' => $cart->order_id,
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'pack' => $pr->pack,
                            'stock' => $productInfo->stock,
                            'total_order' => $total_quantity,
                            'available_stock' => $availableStock,
                            'delivered_quantity' => $total_delivered_quantity,
                            'available_delivered_quantity' => $total_available_delivered_quantity,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                            'images' => $gallleryList,
                            'requested' => $pedidosList
                        ];
                    }


                    $adiscount = 0;
                    $totalamount = 0;

                    if ($cart->discount > 0) {
                        $adiscount = $subTotal * ($cart->discount / 100);
                    }

                    $totalamount = $subTotal - $adiscount;

                    $array[] = [
                        'order_id' => $cart->order_id,
                        'order_number' => $cart->order_number,
                        'order_comments' => $cart->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'adiscount' => number_format($adiscount, 2),
                        'totalamount' => number_format($totalamount, 2),
                        'total_quantity' => (string)$total_quantity,
                        'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                        'discount' => $cart->discount,
                        'discount_a' => $cart->discount_a,
                        'amount' => $cart->amount,
                        'total_amount' => $cart->total_amount,
                        'seller' => $vendorInfo->first_name . " " . $vendorInfo->last_name,
                        'payment_method' => $cart->payment_method,
                        'payment_status' => $cart->payment_status,
                        'transaction_id' => $cart->transaction_id,
                        'order_status' => $cart->order_status,
                        'name_status_spanish' => $cart->name_status_spanish,
                        'name_status_english' => $cart->name_status_english,
                        'scolor' => $cart->color,
                        'discount_type' => $cart->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart->created,
                        'updated' => $cart->updated,
                        'product_list' => $newarray
                    ];
                }

                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'language_id' => $cart->language_id,
                    'company_id' => $cart->company_id,
                    'order_list' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function warehouseOrderDetails_post()
    {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['order_id'] = $this->post('order_id');
            $userData['company_id'] = $this->post('company_id');
        }
        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = (($userData['language_id']) != '') ? $userData['language_id'] : '1';
            $order_id = $userData['order_id'];
            $company_id = $userData['company_id'];

            $userInfo = $this->Apimodel->get_cond('vendors', "company_id='" . $company_id . "'");

            $orderDetails = $this->Apimodel->get_cond("orders", "order_id=" . $userData['order_id'] . " ");

            if (!empty($orderDetails)) {

                $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$orderDetails->order_id'");

                $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$orderDetails->customer_id'");

                $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$orderDetails->user_id'");

                $subTotal = 0;
                $total_quantity = 0;
                $total_dev_quantity = 0;

                $newarray = array();

                foreach ($productList as $key => $pr) {
                    $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");


                    $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");

                    $gallleryList = array();

                    $pedidosList = array();

                    $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                    foreach ($pedidos as $ped) {
                        $pedidosList[] = array(
                            'customer_id' => $ped->customer_id,
                            'qty' => $ped->qty,
                            'requested' => $ped->requested,
                        );
                    }


                    foreach ($imgList as $img) {
                        if ($img->images != "") {
                            $product_pic = base_url() . 'uploads/products/' . $userData['company_id'] . '/' . $img->images;
                        } else {
                            $product_pic = base_url() . 'images/noimg.png';
                        }
                        $gallleryList[] = array(
                            'product_id' => $pr->pro_id,
                            'company_id' => $pr->company_id,
                            'img_id' => $img->img_id,
                            'pic' => $product_pic,
                            'local' => $img->img_id . ".jpg",
                        );
                    }

                    $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                    $subTotal = $totalprice + $subTotal;

                    $total_quantity = $total_quantity + @$pr->qty;

                    $totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE od.order_id='" . $orderDetails->order_id . "'";
                    $totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                    if (!empty($totalDevOrder)) {
                        if ($totalDevOrder->total_quantity) {
                            $total_dev_quantity = @$totalDevOrder->total_quantity;
                        } else {
                            $total_dev_quantity = "0";
                        }
                    } else {
                        $total_dev_quantity = "0";
                    }

                    $total_available_dev_quantity = $total_quantity - $total_dev_quantity;


                    $totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE odt.product_id='" . $pr->product_id . "'  AND od.`order_id`='" . $orderDetails->order_id . "'";
                    $totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

                    if (!empty($totalDeliveredOrder)) {
                        if ($totalDeliveredOrder->total_delivered_quantity) {
                            $total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
                        } else {
                            $total_delivered_quantity = "0";
                        }
                    } else {
                        $total_delivered_quantity = "0";
                    }

                    $availableStock = ($productInfo->stock - $total_delivered_quantity);

                    $total_available_delivered_quantity = $pr->qty - $total_delivered_quantity;

                    $newarray[] = [
                        'currency_type' => $this->getSymbol($productInfo->currency_type),
                        'product_id' => $pr->product_id,
                        'name' => strtoupper($productInfo->name),
                        'sku' => $productInfo->sku,
                        'qty' => $pr->qty,
                        'pack' => $pr->pack,
                        'stock' => $productInfo->stock,
                        'total_order' => $total_quantity,
                        'available_stock' => $availableStock,
                        'delivered_quantity' => $total_delivered_quantity,
                        'delivered_qty' => $pr->delivered_quantity,
                        'delivered_pack' => $pr->delivered_pack,
                        'available_delivered_quantity' => $total_available_delivered_quantity,
                        'sale_price' => $pr->sale_price,
                        'fob_price' => $pr->fob_price,
                        'purchase_price' => $pr->purchase_price,
                        'barcode' => $productInfo->barcode,
                        'discount' => $pr->discount,
                        'discount_type' => $pr->discount_type,
                        'comment' => $pr->comment,
                        'created' => $pr->created,
                        'images' => $gallleryList,
                        'requested' => $pedidosList,
                    ];
                }

                $adiscount = 0;
                $totalamount = 0;

                if ($orderDetails->discount > 0) {
                    $adiscount = $subTotal * ($orderDetails->discount / 100);
                }

                $totalamount = $subTotal - $adiscount;


                $array = [
                    'order_id' => $orderDetails->order_id,
                    'order_number' => $orderDetails->order_number,
                    'order_comments' => $orderDetails->order_comments,
                    'ordered_total' => number_format($subTotal, 2),
                    'adiscount' => number_format($adiscount, 2),
                    'totalamount' => number_format($totalamount, 2),
                    'total_quantity' => (string)$total_quantity,
                    'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                    'discount' => $orderDetails->discount,
                    'seller' => $vendorInfo->first_name . " " . $vendorInfo->last_name,
                    'payment_method' => $orderDetails->payment_method,
                    'payment_status' => $orderDetails->payment_status,
                    'transaction_id' => $orderDetails->transaction_id,
                    'order_status' => $orderDetails->order_status,
                    'discount_type' => $orderDetails->discount_type,
                    'business_name' => $customerInfo->business_name,
                    'customer_id' => $customerInfo->customer_id,
                    'name' => $customerInfo->name,
                    'email' => $customerInfo->email,
                    'phone' => $customerInfo->phone,
                    'commercial_address' => $customerInfo->commercial_address,
                    'commercial_delivery_address' => $customerInfo->commercial_delivery_address,
                    'commercial_country' => $customerInfo->commercial_country,
                    'commercial_state' => $customerInfo->commercial_state,
                    'commercial_city' => $customerInfo->commercial_city,
                    'commercial_zip_code' => $customerInfo->commercial_zip_code,
                    'commercial_zone' => $customerInfo->commercial_zone,
                    'dispatch_address' => $customerInfo->dispatch_address,
                    'dispatch_delivery_address' => $customerInfo->dispatch_delivery_address,
                    'dispatch_country' => $customerInfo->dispatch_country,
                    'dispatch_state' => $customerInfo->dispatch_state,
                    'dispatch_city' => $customerInfo->dispatch_city,
                    'dispatch_zip_code' => $customerInfo->dispatch_zip_code,
                    'dispatch_zone' => $customerInfo->dispatch_zone,
                    'dispatch_shipping_notes' => $customerInfo->dispatch_shipping_notes,
                    'created' => $orderDetails->created,
                    'updated' => $orderDetails->updated,
                    'product_list' => $newarray
                ];


                $array = $this->arrcheck($array);

                $this->response([
                    'status' => "1",
                    'order_info' => $array
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }

    public function warehouseOrderReceivedList_post()
    {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['language_id'] = $this->post('language_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['warehouse_user_id'] = $this->post('warehouse_user_id');
        }

        $this->form_validation->set_rules('language_id', 'Language Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('warehouse_user_id', 'Warehouse User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('language_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('language_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], REST_Controller::HTTP_OK);
            }
            if (form_error('warehouse_user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('warehouse_user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $language_id = $userData['language_id'];
            $company_id = $userData['company_id'];
            $ware_user_id = $userData['warehouse_user_id'];


            // New Order List
            $list = $this->Apimodel->get_cond_all("orders", "company_id=" . $userData['company_id'] . " AND warehouse_user_id='" . $ware_user_id . "' AND order_status='9' ORDER BY updated DESC");

            // Pending Order LIst
            $pendinglist = $this->Apimodel->get_cond_all("orders", "company_id=" . $userData['company_id'] . "  AND warehouse_user_id='" . $ware_user_id . "' AND order_status IN (8,10)");

            $array = array();
            $array2 = array();
            $pendarr = array();

            if (!empty($list) || !empty($pendinglist)) {
                foreach ($list as $cart) {
                    $productList = $this->Apimodel->get_cond_all('order_details', "order_id='$cart->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart->customer_id'");

                    $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$cart->user_id'");

                    $subTotal = 0;
                    $total_quantity = 0;
                    $total_dev_quantity = 0;

                    $newarray = array();

                    foreach ($productList as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $total_quantity = $total_quantity + @$pr->qty;

                        $totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE od.order_id='" . $cart->order_id . "' AND od.company_id='" . $company_id . "'";
                        $totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                        if (!empty($totalDevOrder)) {
                            if ($totalDevOrder->total_quantity) {
                                $total_dev_quantity = @$totalDevOrder->total_quantity;
                            } else {
                                $total_dev_quantity = "0";
                            }
                        } else {
                            $total_dev_quantity = "0";
                        }

                        $total_available_dev_quantity = $total_quantity - $total_dev_quantity;

                        $totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  odt.product_id='" . $pr->product_id . "' AND od.company_id='" . $company_id . "' AND od.`order_id`='" . $cart->order_id . "'";

                        $totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

                        if (!empty($totalDeliveredOrder)) {
                            if ($totalDeliveredOrder->total_delivered_quantity) {
                                $total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
                            } else {
                                $total_delivered_quantity = "0";
                            }
                        } else {
                            $total_delivered_quantity = "0";
                        }

                        $availableStock = ($productInfo->stock - $total_delivered_quantity);

                        $total_available_delivered_quantity = $pr->qty - $total_delivered_quantity;

                        $gallleryList = array();

                        $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");


                        $pedidosList = array();

                        $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                        foreach ($pedidos as $ped) {
                            $pedidosList[] = array(
                                'customer_id' => $ped->customer_id,
                                'qty' => $ped->qty,
                                'requested' => $ped->requested,
                            );
                        }


                        foreach ($imgList as $img) {
                            if ($img->images != "") {
                                $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                            } else {
                                $product_pic = base_url() . 'images/noimg.png';
                            }
                            $gallleryList[] = array(
                                'product_id' => $pr->pro_id,
                                'company_id' => $pr->company_id,
                                'img_id' => $img->img_id,
                                'pic' => $product_pic,
                                'local' => $img->img_id . ".jpg",
                            );
                        }

                        $newarray[] = [
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'pack' => $pr->pack,
                            'stock' => $productInfo->stock,
                            'total_order' => $total_quantity,
                            'available_stock' => $availableStock,
                            'delivered_quantity' => $total_delivered_quantity,
                            'available_delivered_quantity' => $total_available_delivered_quantity,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                            'images' => $gallleryList,
                            'requested' => $pedidosList
                        ];
                    }


                    $fecha1 = new DateTime();
                    $fechaactual = date('d/m/Y');
                    $fechtemp = new DateTime($cart->created);
                    $fechaorden = $fechtemp->format('d/m/Y');

                    $fechupd = new DateTime($cart->updated);
                    $fechaupdate = $fechupd->format('d/m/Y');

                    if ($fechaupdate != $fechaactual) {
                        $new = "S";

                        $pendarr[] = [
                            'order_id' => $cart->order_id,
                            'order_number' => $cart->order_number,
                            'order_comments' => $cart->order_comments,
                            'ordered_total' => number_format($subTotal, 2),
                            'total_quantity' => (string)$total_quantity,
                            'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                            'discount' => $cart->discount,
                            'payment_method' => $cart->payment_method,
                            'payment_status' => $cart->payment_status,
                            'transaction_id' => $cart->transaction_id,
                            'order_status' => $cart->order_status,
                            'delivery_status' => $cart->delivery_status,
                            'discount_type' => $cart->discount_type,
                            'business_name' => $customerInfo->business_name,
                            'customer_id' => $customerInfo->customer_id,
                            'name' => $customerInfo->name,
                            'email' => $customerInfo->email,
                            'phone' => $customerInfo->phone,
                            'created' => $fechaorden,
                            'updated' => $cart->updated,
                            'neworder' => $new,
                            'product_list' => $newarray
                        ];

                    } else {
                        $new = "N";
                        $array[] = [
                            'order_id' => $cart->order_id,
                            'order_number' => $cart->order_number,
                            'order_comments' => $cart->order_comments,
                            'ordered_total' => number_format($subTotal, 2),
                            'total_quantity' => (string)$total_quantity,
                            'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                            'discount' => $cart->discount,
                            'payment_method' => $cart->payment_method,
                            'payment_status' => $cart->payment_status,
                            'transaction_id' => $cart->transaction_id,
                            'order_status' => $cart->order_status,
                            'delivery_status' => $cart->delivery_status,
                            'discount_type' => $cart->discount_type,
                            'business_name' => $customerInfo->business_name,
                            'customer_id' => $customerInfo->customer_id,
                            'name' => $customerInfo->name,
                            'email' => $customerInfo->email,
                            'phone' => $customerInfo->phone,
                            'created' => $fechaorden,
                            'updated' => $cart->updated,
                            'neworder' => $new,
                            'product_list' => $newarray
                        ];


                    }

                }

                // For Pending Order List Loop
                //$pendinglist = $list;
                foreach ($pendinglist as $cart2) {
                    $productList2 = $this->Apimodel->get_cond_all('order_details', "order_id='$cart2->order_id'");

                    $customerInfo = $this->Apimodel->get_cond('customers', "customer_id='$cart2->customer_id'");

                    $vendorInfo = $this->Apimodel->get_cond('vendors', "user_id='$cart2->user_id'");

                    $subTotal = 0;
                    $total_quantity = 0;
                    $total_dev_quantity = 0;

                    $newarray2 = array();

                    foreach ($productList2 as $key => $pr) {
                        $productInfo = $this->Apimodel->get_cond('products', "product_id='$pr->product_id'");

                        $totalprice = ((@$pr->qty) * (@$pr->sale_price));
                        $subTotal = $totalprice + $subTotal;

                        $total_quantity = $total_quantity + @$pr->qty;

                        $totalDevQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  od.order_id='" . $cart2->order_id . "' AND od.company_id='" . $company_id . "'";
                        $totalDevOrder = $this->Apimodel->fetch_single_join($totalDevQuantitySql);

                        if (!empty($totalDevOrder)) {
                            if ($totalDevOrder->total_quantity) {
                                $total_dev_quantity = @$totalDevOrder->total_quantity;
                            } else {
                                $total_dev_quantity = "0";
                            }
                        } else {
                            $total_dev_quantity = "0";
                        }

                        $total_available_dev_quantity = $total_quantity - $total_dev_quantity;


                        $totalDeliveredQuantitySql = "SELECT SUM(odt.delivered_quantity) AS total_delivered_quantity FROM `orders` as od INNER JOIN `delivered_orders` as odt ON odt.order_id=od.order_id WHERE  odt.product_id='" . $pr->product_id . "' AND od.company_id='" . $company_id . "' AND od.`order_id`='" . $cart2->order_id . "'";
                        $totalDeliveredOrder = $this->Apimodel->fetch_single_join($totalDeliveredQuantitySql);

                        if (!empty($totalDeliveredOrder)) {
                            if ($totalDeliveredOrder->total_delivered_quantity) {
                                $total_delivered_quantity = @$totalDeliveredOrder->total_delivered_quantity;
                            } else {
                                $total_delivered_quantity = "0";
                            }
                        } else {
                            $total_delivered_quantity = "0";
                        }

                        $availableStock = ($productInfo->stock - $total_delivered_quantity);

                        $total_available_delivered_quantity = $pr->qty - $total_delivered_quantity;

                        $gallleryList = array();

                        $imgList = $this->Apimodel->get_cond_all('product_images', "product_id=$pr->product_id");


                        $pedidosList = array();

                        $pedidos = $this->Apimodel->get_cond_all('rs_requested', "product_id=$pr->product_id ");


                        foreach ($pedidos as $ped) {
                            $pedidosList[] = array(
                                'customer_id' => $ped->customer_id,
                                'qty' => $ped->qty,
                                'requested' => $ped->requested,
                            );
                        }


                        foreach ($imgList as $img) {
                            if ($img->images != "") {
                                $product_pic = base_url() . 'uploads/products/' . $company_id . '/' . $img->images;
                            } else {
                                $product_pic = base_url() . 'images/noimg.png';
                            }
                            $gallleryList[] = array(
                                'product_id' => $pr->pro_id,
                                'company_id' => $pr->company_id,
                                'img_id' => $img->img_id,
                                'pic' => $product_pic,
                                'local' => $img->img_id . ".jpg",
                            );
                        }

                        $newarray2[] = [
                            'currency_type' => $this->getSymbol($productInfo->currency_type),
                            'product_id' => $pr->product_id,
                            'name' => strtoupper($productInfo->name),
                            'sku' => $productInfo->sku,
                            'qty' => $pr->qty,
                            'pack' => $pr->pack,
                            'stock' => $productInfo->stock,
                            'total_order' => $total_quantity,
                            'available_stock' => $availableStock,
                            'delivered_quantity' => $total_delivered_quantity,
                            'available_delivered_quantity' => $total_available_delivered_quantity,
                            'sale_price' => $pr->sale_price,
                            'fob_price' => $pr->fob_price,
                            'purchase_price' => $pr->purchase_price,
                            'barcode' => $productInfo->barcode,
                            'discount' => $pr->discount,
                            'discount_type' => $pr->discount_type,
                            'discount' => $pr->discount,
                            'comment' => $pr->comment,
                            'created' => $pr->created,
                            'images' => $gallleryList,
                            'requested' => $pedidosList
                        ];
                    }
                    $new = "N";
                    $array2[] = [
                        'order_id' => $cart2->order_id,
                        'order_number' => $cart2->order_number,
                        'order_comments' => $cart2->order_comments,
                        'ordered_total' => number_format($subTotal, 2),
                        'total_quantity' => (string)$total_quantity,
                        'total_available_delivered_quantity' => (string)$total_available_dev_quantity,
                        'discount' => $cart2->discount,
                        'payment_method' => $cart2->payment_method,
                        'payment_status' => $cart2->payment_status,
                        'transaction_id' => $cart2->transaction_id,
                        'order_status' => $cart2->order_status,
                        'delivery_status' => $cart2->delivery_status,
                        'discount_type' => $cart2->discount_type,
                        'business_name' => $customerInfo->business_name,
                        'customer_id' => $customerInfo->customer_id,
                        'name' => $customerInfo->name,
                        'email' => $customerInfo->email,
                        'phone' => $customerInfo->phone,
                        'created' => $cart2->created,
                        'updated' => $cart2->updated,
                        'neworder' => $new,
                        'product_list' => $newarray2
                    ];


                }

                $array = $this->arrcheck($array);
                $array2 = $this->arrcheck($array2);
                $pendarr = $this->arrcheck($pendarr);

                $this->response([
                    'status' => "1",
                    'language_id' => $language_id,
                    'company_id' => $company_id,
                    'new_order_list' => $array,
                    'pending_order_list' => $pendarr
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No Order found.'
                ], REST_Controller::HTTP_OK);
            }
        }
    }


    public function saveStatusOrder_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['status_id'] = $this->post('status_id');
        }

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');
        $this->form_validation->set_rules('status_id', 'Status', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('status_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('status_id'))
                ], REST_Controller::HTTP_OK);
            }


        } else {

            $order_id = $userData['order_id'];
            $status_id = $userData['status_id'];

            if ($status_id == "10") {
                $status_id = "9";
            }

            $product = $this->Apimodel->get_cond('orders', "order_id=" . $order_id . "");

            if (!empty($product)) {

                $stockSql = "UPDATE `orders` SET `order_status`= '" . $status_id . "' WHERE  `order_id`='" . $order_id . "'";
                $update = $this->db->query($stockSql);


                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Order updated successfully.',
                        'product_id' => $order_id,
                        'stock' => @$status_id,
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function updateOrderStatus_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['order_status'] = $this->post('order_status');
            $userData['delivery_status'] = $this->post('delivery_status');
            $userData['productlist'] = $this->post('productlist');
        }

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');
        $this->form_validation->set_rules('order_status', 'Order Status', 'trim|required');
        $this->form_validation->set_rules('delivery_status', 'Delivery Status', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('order_status')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_status'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('delivery_status')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('delivery_status'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $order_id = $userData['order_id'];
            $order_status = $userData['order_status'];
            $delivery_status = $userData['delivery_status'];

            if ($delivery_status == "0") {
                $order_status = "0";
            } else {
                $order_status = $userData['order_status'];
            }

            $orders = $this->Apimodel->get_cond('orders', "order_id=" . $order_id . "");

            $leido = 1;

            if (!empty($orders)) {
                $updateData = array(
                    'order_status' => $order_status,
                    'delivery_status' => $delivery_status,
                    'leido' => $leido,
                );

                $where = "order_id = $order_id";

                $update = $this->Apimodel->update_cond("orders", $where, $updateData);

                if (!empty($userData['productlist'])) {

                    foreach ($userData['productlist'] as $key => $proArray) {

                        $product_id = @$userData['productlist'][$key]['product_id'];
                        $delivered_quantity = @$userData['productlist'][$key]['delivered_quantity'];
                        $delivered_pack = @$userData['productlist'][$key]['delivered_pack'];

                        $productArray = array(
                            'order_id' => $order_id,
                            'product_id' => $product_id,
                            'delivered_quantity' => $delivered_quantity,
                            'delivered_pack' => $delivered_pack,
                            'delivered_date' => date('Y-m-d h:i:s')
                        );

                        $updateData2 = array(
                            'delivered_quantity' => $delivered_quantity,
                            'delivered_pack' => $delivered_pack,
                            'pack' => $delivered_pack,
                            'delivered_date' => date('Y-m-d h:i:s')
                        );

                        $where2 = "order_id = $order_id AND product_id=$product_id";

                        $where3 = "order_id = $order_id AND qty=0";

                        // $this->Apimodel->update_cond("order_details", $where2, $productArray);
                        $this->Apimodel->delete_single_con('delivered_orders', $where2);
                        $this->Apimodel->add_details('delivered_orders', $productArray);
                        $this->Apimodel->delete_single_con('order_details', $where3);
                        $update2 = $this->Apimodel->update_cond("order_details", $where2, $updateData2);
                    }

                }

                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Order Dispatch successfully.',
                        'order_id' => $order_id,
                        'order_status' => $order_status,
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function updateInventoryStatus_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['order_status'] = $this->post('order_status');
            $userData['delivery_status'] = $this->post('delivery_status');
            $userData['productlist'] = $this->post('productlist');
        }

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');
        $this->form_validation->set_rules('order_status', 'Order Status', 'trim|required');
        $this->form_validation->set_rules('delivery_status', 'Delivery Status', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('order_status')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_status'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('delivery_status')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('delivery_status'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $order_id = $userData['order_id'];
            $order_status = $userData['order_status'];
            $delivery_status = $userData['delivery_status'];

            if ($delivery_status == "0") {
                $order_status = "0";
            } else {
                $order_status = $userData['order_status'];
            }

            $orders = $this->Apimodel->get_cond('orders', "order_id=" . $order_id . "");

            if (!empty($orders)) {
                $updateData = array(
                    'order_status' => $order_status,
                    'delivery_status' => $delivery_status,
                );

                $where = "order_id = $order_id";

                $update = $this->Apimodel->update_cond("orders", $where, $updateData);

                if (!empty($userData['productlist'])) {

                    foreach ($userData['productlist'] as $key => $proArray) {

                        $product_id = @$userData['productlist'][$key]['product_id'];
                        $delivered_quantity = @$userData['productlist'][$key]['delivered_quantity'];
                        $delivered_pack = @$userData['productlist'][$key]['delivered_pack'];

                        $productArray = array(
                            'order_id' => $order_id,
                            'product_id' => $product_id,
                            'delivered_quantity' => $delivered_quantity,
                            'delivered_pack' => $delivered_pack,
                            'delivered_date' => date('Y-m-d h:i:s')
                        );

                        $updateData2 = array(
                            'qty' => $delivered_quantity,
                            'pack' => $delivered_pack,
                            'delivered_quantity' => $delivered_quantity,
                            'delivered_pack' => $delivered_pack,
                            'pack' => $delivered_pack,
                            'delivered_date' => date('Y-m-d h:i:s')
                        );

                        $where2 = "order_id = $order_id AND product_id=$product_id";

                        // $this->Apimodel->update_cond("order_details", $where2, $productArray);
                        $this->Apimodel->delete_single_con('delivered_orders', $where2);
                        $this->Apimodel->add_details('delivered_orders', $productArray);
                        $update2 = $this->Apimodel->update_cond("order_details", $where2, $updateData2);
                    }

                }

                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Order Dispatch successfully.',
                        'order_id' => $order_id,
                        'order_status' => $order_status,
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }


    public function updateInventoryStock_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['product_id'] = $this->post('product_id');
            $userData['warehouse_user_id'] = $this->post('warehouse_user_id');
            $userData['stock_quantity'] = $this->post('stock_quantity');
        }

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');
        $this->form_validation->set_rules('product_id', 'Product Id', 'trim|required');
        $this->form_validation->set_rules('warehouse_user_id', 'Warehouse User Id', 'trim|required');
        $this->form_validation->set_rules('stock_quantity', 'Stock Quantity', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('product_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('warehouse_user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('warehouse_user_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('stock_quantity')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('stock_quantity'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $order_id = $userData['order_id'];
            $product_id = $userData['product_id'];
            $stock_quantity = $userData['stock_quantity'];
            $user_id = $userData['warehouse_user_id'];

            $product = $this->Apimodel->get_cond('products', "product_id=" . $product_id . "");

            if (!empty($product)) {

                $stockSql = "UPDATE `order_details` SET `qty`= '" . $stock_quantity . "' WHERE `product_id`='" . $product_id . "' and `order_id`='" . $order_id . "'";
                $update = $this->db->query($stockSql);

                $stockSql1 = "UPDATE `orders` SET `order_status`= '16' WHERE `order_id`='" . $order_id . "'";
                $update1 = $this->db->query($stockSql1);

                $updateProduct = $this->Apimodel->get_cond('products', "product_id=" . $product_id . "");

                if ($update) {
                    $this->response([
                        'status' => "1",
                        'message' => 'Stock updated successfully.',
                        'product_id' => $product_id,
                        'stock' => @$updateProduct->stock,
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again."
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No record was found.'
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function deleteOrder_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['order_id'] = $this->post('order_id');
            $userData['user_id'] = $this->post('user_id');
        }

        $this->form_validation->set_rules('order_id', 'Order Id', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {

            if (form_error('order_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('order_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {

            $order_id = $userData['order_id'];
            $user_id = $userData['user_id'];

            $orderInfo = $this->Apimodel->get_cond('orders', "order_id=" . $order_id . " AND user_id=" . $user_id . " ");

            if (empty($orderInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'Invalid Order Id'
                ], REST_Controller::HTTP_OK);

            } else {

                $where = array(
                    'order_id' => $order_id
                );

                $delete = $this->Apimodel->delete_single_con('orders', $where);

                if ($delete) {

                    $this->Apimodel->delete_single_con('order_details', $where);
                    $this->response([
                        'status' => "1",
                        'message' => 'Order removed successfully'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => 'Opps, Some thing went wrong'
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }

    public function deleteOrderAll_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }
        } else {

            $user_id = $userData['user_id'];

            $orderList = $this->Apimodel->get_cond_all('orders', "user_id=" . $user_id . " ");

            if (empty($orderList)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No orders found'
                ], REST_Controller::HTTP_OK);

            } else {

                $where = array(
                    'user_id' => $user_id
                );

                $delete = $this->Apimodel->delete_single_con('orders', $where);

                if ($delete) {

                    foreach ($orderList as $key => $ord) {
                        $wh = array(
                            'order_id' => $ord->order_id,
                        );
                        $this->Apimodel->delete_single_con('order_details', $wh);
                    }


                    $this->response([
                        'status' => "1",
                        'message' => 'Order removed successfully'
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => "0",
                        'error' => 'Opps, Some thing went wrong'
                    ], REST_Controller::HTTP_OK);
                }
            }
        }
    }

    public function addRequestCatalog_post()
    {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['customer_id'] = $this->post('customer_id');
            $userData['descriptions'] = $this->post('descriptions');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('customer_id', 'Customer Id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], REST_Controller::HTTP_OK);
            }

            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], REST_Controller::HTTP_OK);
            }

        } else {

            $customer_id = $userData['customer_id'];
            $user_id = $userData['user_id'];
			$vendors = $this->Apimodel->get_cond('vendors', "user_id=".$user_id."");

            if (!empty($vendors)) {
                $mydata = array(
                    'user_id' => $userData['user_id'],
                    'customer_id' => $userData['customer_id'],
                    'descriptions' => @$userData['descriptions'],
                );

                $insert = $this->Apimodel->add_details('catalog_request', $mydata);
                $company = $this->mymodel->get_by("companies",true,["company_id"=>$vendors->company_id]);
                $customer = $this->mymodel->get_by("customers",true,["customer_id"=>$userData['customer_id']]);
                $catalog = $this->mymodel->get_by('catalog', true, ['company_id'=>$company->company_id,'default_catalog'=>1]);

                if ($insert) {
                    $this->CatalogoModel->sendMailCatalog($company, $customer ,$vendors, $catalog->catalog_id, $company->catalogsend_time);

                    //$consulta = "select c.* from customers a left join (select MAX(cart_id) as cart_id, customer_id from catalog_cart group by customer_id) b on b.customer_id = a.customer_id left join (select * from catalog_cart order by cart_id desc) c on c.cart_id = b.cart_id where a.customer_id = ".$userData['customer_id']." and ifnull(b.cart_id,0)>0";
                    $consulta = "select * from catalog_cart where customer_id = ".$userData['customer_id']." order by cart_id desc limit 1";
                    $list = $this->Apimodel->fetch_all_join($consulta);

                     $data = array();

                    // Recorrer cada fila de los resultados
                    foreach ($list as $fila) {
                        // Array para almacenar los valores de esta fila
                        $fila_array = array();

                        // Recorrer cada columna de la fila
                        foreach ($fila as $nombre_campo => $valor) {
                            // Añadir el valor al array asociativo usando el nombre del campo como clave
                            $fila_array[$nombre_campo] = $valor;
                        }

                        // Añadir el array de esta fila al array de datos
                        $data[] = $fila_array;
                    }

                    $data = $this->arrcheck($data);


                    $this->response([
                        'status' => "1",
                        'error' => "",
                        'message' => 'Catalog request sent successfully.',
                        'request_id' => strval($insert),
                        'token' => $list[0]->token_kor,
                        'url' => base_url().'catalogo/producto?token='.$list[0]->token_kor,
                        'catalog_cart' => $data
                    ], REST_Controller::HTTP_OK);

                } else {
                    $this->response([
                        'status' => "0",
                        'error' => "Some problems occurred, please try again.",
                        'message' => "",
                        'request_id' => 0,
                        'token' => "",
                        'url' => "",
                        'catalog_cart' => $data
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => "0",
                    'error' => 'No vendor was found.',
                    'message' => "",
                    'request_id' => 0,
                    'token' => "",
                    'url' => "",
                    'catalog_cart' => $data
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
    }

    public function createCustomerOffline_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData = $this->input->post();
        }

        if (empty($userData)) {
            $this->response([
                'status' => "0",
                'error' => "Customer List is required"
            ], 400);

        }

        $insert = false;

        foreach ($userData as $ind => $val) {
            $customerInfo = $this->Apimodel->get_cond('customers', "email='" . $val['email'] . "'");

            if (empty($customerInfo)) {

                $mydata = array(
                    'business_name' => $val['business_name'],
                    'language_id' => $val['language_id'],
                    'company_id' => $val['company_id'],
                    'user_id' => $val['user_id'],
                    'name' => $val['name'],
                    'tax_id' => $val['tax_id'],
                    'discount' => $val['discount'],
                    'term_id' => $val['term_id'],
                    'group_id' => $val['group_id'],
                    'email' => $val['email'],
                    'phone' => $val['phone'],
                    'cell_phone' => $val['cell_phone'],
                    'notes' => $val['notes'],
                    'commercial_address' => $val['commercial_address'],
                    'commercial_country' => $val['commercial_country'],
                    'commercial_state' => $val['commercial_state'],
                    'commercial_city' => $val['commercial_city'],
                    'commercial_zone' => $val['commercial_zone'],
                    'commercial_zip_code' => $val['commercial_zip_code'],
                    'dispatch_address' => $val['dispatch_address'],
                    'dispatch_country' => $val['dispatch_country'],
                    'dispatch_state' => $val['dispatch_state'],
                    'dispatch_city' => $val['dispatch_city'],
                    'dispatch_zone' => $val['dispatch_zone'],
                    'dispatch_zip_code' => $val['dispatch_zip_code'],
                    'customer_created_at' => date("Y-m-d H:i:s"),
                    'customer_status' => 1
                );

                $insert = $this->Apimodel->add_details('customers', $mydata);
            }
        }

        if ($insert) {
            $this->response([
                'status' => "1",
                'total' => count($userData),
                'message' => 'Customers added successfully.',
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => "Some problems occurred, please try again."
            ], REST_Controller::HTTP_OK);
        }

    }

    public function addRequestCatalogOffline_post()
    {

        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData = $this->input->post();
        }

        if (empty($userData)) {
            $this->response([
                'status' => "0",
                'error' => "Request List is required"
            ], 400);

        }

        $insert = false;

        foreach ($userData as $ind => $val) {
            $mydata = array(
                'user_id' => $val['user_id'],
                'customer_id' => $val['customer_id'],
                'descriptions' => @$val['descriptions'],
                'created' => date("Y-m-d H:i:s"),
            );

            $insert = $this->Apimodel->add_details('catalog_request', $mydata);

        }

        if ($insert) {
            $this->response([
                'status' => "1",
                'message' => 'Catalog request sent successfully.',
                'total' => count($userData),
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => "Some problems occurred, please try again."
            ], REST_Controller::HTTP_OK);
        }

    }

    public function editCustomerOffline_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData = $this->input->post();
        }

        if (empty($userData)) {
            $this->response([
                'status' => "0",
                'error' => "Customer List is required"
            ], 400);

        }

        $update = false;

        foreach ($userData as $ind => $val) {
            $where = array(
                'customer_id' => $val['customer_id']
            );

            $mydata = array(
                'language_id' => $val['language_id'],
                'company_id' => $val['company_id'],
                'user_id' => $val['user_id'],
                'name' => $val['name'],
                'business_name' => $val['business_name'],
                'tax_id' => $val['tax_id'],
                'discount' => $val['discount'],
                'term_id' => $val['term_id'],
                'group_id' => $val['group_id'],
                'phone' => $val['phone'],
                'cell_phone' => $val['cell_phone'],
                'notes' => $val['notes'],
                'commercial_address' => $val['commercial_address'],
                'commercial_country' => $val['commercial_country'],
                'commercial_state' => $val['commercial_state'],
                'commercial_city' => $val['commercial_city'],
                'commercial_zone' => $val['commercial_zone'],
                'commercial_zip_code' => $val['commercial_zip_code'],
                'dispatch_address' => $val['dispatch_address'],
                'dispatch_country' => $val['dispatch_country'],
                'dispatch_state' => $val['dispatch_state'],
                'dispatch_city' => $val['dispatch_city'],
                'dispatch_zone' => $val['dispatch_zone'],
                'dispatch_zip_code' => $val['dispatch_zip_code'],
            );

            $update = $this->Apimodel->edit_single_row('customers', $mydata, $where);
        }

        if ($update) {
            $this->response([
                'status' => "1",
                'message' => 'Customer updated successfully.'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => "Some problems occurred, please try again."
            ], REST_Controller::HTTP_OK);
        }
    }

    public function customerDeleteOffline_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData = $this->input->post();
        }

        if (empty($userData)) {
            $this->response([
                'status' => "0",
                'error' => "Customer List is required"
            ], 400);

        }

        $delete = false;

        foreach ($userData as $ind => $val) {
            $where = array(
                'customer_id' => $val['customer_id']
            );

            $delete = $this->Apimodel->delete_single_con('customers', $where);
        }

        if ($delete) {

            $this->response([
                'status' => "1",
                'message' => 'Customer deleted successfully'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => 'Opps, Some thing went wrong'
            ], REST_Controller::HTTP_OK);
        }

    }

    protected function validateAddOrdersOfflineRequest($requestOrders)
    {
        if (empty($requestOrders)) {

            return [
                'content'=>[
                    'status' => "0",
                    'error' => "Order List is required"
                    ],
                'http_code' => self::HTTP_BAD_REQUEST
            ];
        }

        return true;
    }

    public function addOrderOffline_post()
    {
        $requestOrders = $this->getOrderDataFromRequest();

        if(($response = $this->validateAddOrdersOfflineRequest($requestOrders))===true) {
            $response = ['http_code' => self::HTTP_OK];
            $addedOrders = [];
            foreach($requestOrders as $ind => $orderData) {
                $customer = $this->Apimodel->get_cond('customers', "customer_id=" . $orderData['customer_id']);

                $newOrder = $this->OrdenModel->makeOrder(
                    $orderData['user_id'],
                    $customer->customer_id,
                    $orderData['company_id'],
                    $orderData['uuid'],
                    $this->OrdenModel::STANDARD_ORDER,
                    $orderData['discount_type'],
                    $orderData['discount'],
                    $orderData['order_comment'],
                    'app'
                );
                $newOrder->language_id = $orderData['language_id'];
                $newOrder->payment_status = 1;

                $itemList = $this->prepareOrderDetails($orderData['itemList']);
                if($this->saveOrder($newOrder, $customer, $itemList))
                {
                    $addedOrders[]=$newOrder;
                }

            }
            if(count($addedOrders)>0)
            {
                $response['content'] = [
                    'message' => 'New orders generated successfully.',
                    'total' => count($addedOrders)
                ];
            }else {
                $response['content'] =  [
                    'status' => "0",
                    'error' => "Some problems occurred, please try again."
                ];
            }
        }

        $this->response($response['content'], $response['http_code']);
    }

    public function addEditCartOffline_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData = $this->input->post();
        }

        if (empty($userData)) {
            $this->response([
                'status' => "0",
                'error' => "Cart List is required"
            ], 400);

        }

        $insert = false;

        foreach ($userData as $ind => $val) {
            $checkCart = $this->Apimodel->get_cond('cart', "product_id=" . $val['product_id'] . " AND user_id=" . $val['user_id'] . "");

            if (!empty($checkCart)) {
                $updateData = array(
                    'qty' => $val['qty'],
                    'comments' => @$val['comments'],
                );

                $where = "cart_id = $checkCart->cart_id";

                $this->Apimodel->update_cond("cart", $where, $updateData);
                $insert = $checkCart->cart_id;
            } else {
                $mydata = array(
                    'language_id' => $val['language_id'],
                    'product_id' => $val['product_id'],
                    'user_id' => $val['user_id'],
                    'qty' => $val['qty'],
                    'comments' => @$val['comments'],
                    'created' => date("Y-m-d H:i:s")
                );
                $insert = $this->Apimodel->add_details('cart', $mydata);
            }

        }

        if ($insert) {
            $this->response([
                'status' => "1",
                'message' => 'updated successfully.',
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => "0",
                'error' => "Some problems occurred, please try again."
            ], REST_Controller::HTTP_OK);
        }
    }

    public function updateTrackStatus_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);

        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['user_id'] = $this->post('user_id');
            $userData['company_id'] = $this->post('company_id');
            $userData['track_type'] = $this->post('track_type');
            $userData['notes'] = $this->post('notes');
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|required');
        $this->form_validation->set_rules('company_id', 'Company Id', 'trim|required');
        $this->form_validation->set_rules('track_type', 'Track Type', 'trim|required');
        $this->form_validation->set_rules('notes', 'notes', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('user_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('user_id'))
                ], 400);
            }

            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }

            if (form_error('track_type')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('track_type'))
                ], 400);
            }

            if (form_error('notes')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('notes'))
                ], 400);
            }

        } else {
            $user_id = $userData['user_id'];
            $company_id = $userData['company_id'];
            $track_type = $userData['track_type'];

            $userInfo = $this->Apimodel->get_cond('vendors', "user_id='" . $user_id . "'");

            if (empty($userInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => "Seller info is not found!"
                ], 400);

            }

            $companyInfo = $this->Apimodel->get_cond('companies', "company_id='" . $company_id . "'");

            if (empty($companyInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => "Company info is not found!"
                ], 400);

            }

            $mydata = array(
                'user_id' => $user_id,
                'company_id' => $company_id,
                'log_type' => $track_type,
                'notes' => $userData['notes'],
                'created_on' => date("Y-m-d H:i:s")
            );

            $res = $this->Apimodel->add_details("logs", $mydata);

            if ($res) {
                $this->response([
                    'status' => "1",
                    'message' => 'Track added successfully.',
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => "0",
                    'error' => "Some problems occurred, please try again."
                ], 400);
            }

        }
    }

    public function getGroupInformation_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('customer_id', 'customer_id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], 400);
            }

        } else {

            $custInfo = $this->Apimodel->get_cond('customers', "customer_id='" . $userData['customer_id'] . "'");

            if (empty($custInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No customer details was found.'
                ], 400);
            }

            $groupInfo = $this->Apimodel->get_cond('customer_groups', "group_id='" . $custInfo->group_id . "'");

            if (empty($groupInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No group details was found.'
                ], 400);
            }


            $groups = array(
                'group_id' => $groupInfo->group_id,
                'language_id' => $groupInfo->language_id,
                'company_id' => $groupInfo->company_id,
                'user_id' => $groupInfo->user_id,
                'name' => $groupInfo->name,
                'percentage_on_price' => $groupInfo->percentage_on_price,
                'percent_price_amount' => $groupInfo->percent_price_amount,
                'created_at' => $groupInfo->created_at,
                'group_status' => $groupInfo->group_status,
            );

            $groups = $this->removeNull($groups);

            $this->response([
                'status' => "1",
                'details' => $groups
            ], 200);

        }
    }

    public function getProductStatus_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['product_id'] = $this->post('product_id');
        }

        $this->form_validation->set_rules('product_id', 'product_id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('product_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('product_id'))
                ], 400);
            }

        } else {

            $proInfo = $this->Apimodel->get_cond('products', "product_id='" . $userData['product_id'] . "'");

            if (empty($proInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No product details was found.'
                ], 400);
            }


            $groups = array(
                'product_id' => $proInfo->product_id,
                'language_id' => $proInfo->language_id,
                'company_id' => $proInfo->company_id,
                'notify_minimum_stock' => $proInfo->notify_minimum_stock,
            );

            $groups = $this->removeNull($groups);

            $this->response([
                'status' => "1",
                'details' => $groups
            ], 200);

        }

    }

    public function getCompanyInfo_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('company_id', 'company_id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }

        } else {

            $compInfo = $this->Apimodel->get_cond('companies', "company_id='" . $userData['company_id'] . "'");
            $CGInfo = $this->Apimodel->get_cond('customer_groups', "company_id='" . $userData['company_id'] . "' AND default<>0");
            $TSInfo = $this->Apimodel->get_cond('terms_of_sales', "company_id='" . $userData['company_id'] . "' AND default<>0");

            if (empty($compInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No company details was found.'
                ], 400);
            }
            $totcustomers = $this->db->query("select * from `customers` where `company_id` = '" . $userData['company_id'] . "'")->num_rows();

            $totcurrmnthorders = $this->db->query("select * from `orders` where `company_id` = '" . $userData['company_id'] . "' AND (MONTH(created)=MONTH(now()) AND YEAR(created)=YEAR(now()))")->num_rows();


            $company = array(
                'category_id' => $compInfo->category_id,
                'language_id' => $compInfo->language_id,
                'company_id' => $compInfo->company_id,
                'name' => $compInfo->name,
                'short_name' => $compInfo->short_name,
                'email' => $compInfo->email,
                'phone_number' => $compInfo->phone_number,
                'cell_number' => $compInfo->cell_number,
                'max_number_of_clients' => $compInfo->max_number_of_clients,
                'max_number_of_orders' => $compInfo->max_number_of_orders,
                'max_number_of_vendors' => $compInfo->max_number_of_vendors,
                'total_price' => $compInfo->total_price,
                'website' => $compInfo->website,
                'current_total_customer' => strval($totcustomers),
                'current_month_total_orders' => strval($totcurrmnthorders),
                'group_defaul_id' => $CGInfo->group_id,
                'group_defaul_name' => $CGInfo->name,
                'term_defaul_id' => $TSInfo->term_id,
                'term_defaul_name' => $TSInfo->name,

            );

            $company = $this->removeNull($company);

            $this->response([
                'status' => "1",
                'details' => $company
            ], 200);

        }

    }


    public function myCompanyInfo_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['company_id'] = $this->post('company_id');
        }

        $this->form_validation->set_rules('company_id', 'company_id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('company_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('company_id'))
                ], 400);
            }

        } else {

            $compInfo = $this->Apimodel->get_cond('companies', "company_id='" . $userData['company_id'] . "'");
            $CGInfo = $this->Apimodel->get_cond('customer_groups', "company_id='" . $userData['company_id'] . "' AND default<>0");
            $TSInfo = $this->Apimodel->get_cond('terms_of_sales', "company_id='" . $userData['company_id'] . "' AND default<>0");

            if (empty($compInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No company details was found.'
                ], 400);
            }
            $totcustomers = $this->db->query("select * from `customers` where `company_id` = '" . $userData['company_id'] . "'")->num_rows();

            $totcurrmnthorders = $this->db->query("select * from `orders` where `company_id` = '" . $userData['company_id'] . "' AND (MONTH(created)=MONTH(now()) AND YEAR(created)=YEAR(now()))")->num_rows();


            $company = array(
                'category_id' => $compInfo->category_id,
                'language_id' => $compInfo->language_id,
                'company_id' => $compInfo->company_id,
                'name' => $compInfo->name,
                'short_name' => $compInfo->short_name,
                'email' => $compInfo->email,
                'phone_number' => $compInfo->phone_number,
                'cell_number' => $compInfo->cell_number,
                'max_number_of_clients' => $compInfo->max_number_of_clients,
                'max_number_of_orders' => $compInfo->max_number_of_orders,
                'max_number_of_vendors' => $compInfo->max_number_of_vendors,
                'total_price' => $compInfo->total_price,
                'website' => $compInfo->website,
                'current_total_customer' => strval($totcustomers),
                'current_month_total_orders' => strval($totcurrmnthorders),
                'group_defaul_id' => $CGInfo->group_id,
                'group_defaul_name' => $CGInfo->name,
                'term_defaul_id' => $TSInfo->term_id,
                'term_defaul_name' => $TSInfo->name,

            );

            $company = $this->removeNull($company);

            $this->response([
                'status' => "1",
                'details' => $company
            ], 200);

        }

    }

    public function getCustomerGroupInfo_post()
    {
        $json = file_get_contents('php://input');
        $obj = json_decode($json, true);
        if (is_array($obj)) {
            $_POST = (array)$obj;
            $userData = $_POST;
        } else {
            $userData['customer_id'] = $this->post('customer_id');
        }

        $this->form_validation->set_rules('customer_id', 'customer_id', 'trim|required');

        if ($this->form_validation->run() === false) {
            if (form_error('customer_id')) {
                $this->response([
                    'status' => "0",
                    'error' => strip_tags(form_error('customer_id'))
                ], 400);
            }

        } else {

            $custInfo = $this->Apimodel->get_cond('customers', "customer_id ='" . $userData['customer_id'] . "'");

            if (empty($custInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No customer details was found.'
                ], 400);
            }

            $groupId = @$custInfo->group_id;
            $grpInfo = $this->Apimodel->get_cond('customer_groups', "group_id ='" . $groupId . "'");


            if (empty($grpInfo)) {
                $this->response([
                    'status' => "0",
                    'error' => 'No customer group details was found.'
                ], 400);
            }


            $groups = array(
                'group_id' => $grpInfo->group_id,
                'language_id' => $grpInfo->language_id,
                'company_id' => $grpInfo->company_id,
                'name' => $grpInfo->name,
                'percentage_on_price' => $grpInfo->percentage_on_price,
                'percent_price_amount' => $grpInfo->percent_price_amount,
            );

            $groups = $this->removeNull($groups);

            $this->response([
                'status' => "1",
                'details' => $groups
            ], 200);

        }

    }

    public function getSymbol($id)
    {
        $curInfo = $this->Apimodel->get_cond('currency', "currency_id='" . $id . "'");
        if (!empty($curInfo)) {
            $symbol = $curInfo->symbol;

        } else {
            $symbol = "$";
        }

        return $symbol;
    }


    public function get_currency_symbol($cc = 'USD')
    {
        $cc = strtoupper($cc);
        $currency = array(
            "USD" => "$", //U.S. Dollar
            "AUD" => "A$", //Australian Dollar
            "BRL" => "R$", //Brazilian Real
            "CAD" => "C$", //Canadian Dollar
            "XCD" => "X$", //Caribbean island currency Dollar
            "CZK" => "Kč", //Czech Koruna
            "DKK" => "kr", //Danish Krone
            "EUR" => "€", //Euro
            "HKD" => "&#36", //Hong Kong Dollar
            "HUF" => "Ft", //Hungarian Forint
            "ILS" => "₪", //Israeli New Sheqel
            "INR" => "₹", //Indian Rupee
            "JPY" => "¥", //Japanese Yen
            "MYR" => "RM", //Malaysian Ringgit
            "MXN" => "&#36", //Mexican Peso
            "NOK" => "kr", //Norwegian Krone
            "NZD" => "&#36", //New Zealand Dollar
            "PHP" => "₱", //Philippine Peso
            "PLN" => "zł", //Polish Zloty
            "GBP" => "£", //Pound Sterling
            "SEK" => "kr", //Swedish Krona
            "CHF" => "Fr", //Swiss Franc
            "TWD" => "$", //Taiwan New Dollar
            "THB" => "฿", //Thai Baht
            "TRY" => "₺", //Turkish Lira
        );

        if (array_key_exists($cc, $currency)) {
            return $currency[$cc];
        }
    }

    public function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d')
    {

        $dates = array();
        $current = strtotime($first);
        $last = strtotime($last);

        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }

        return $dates;
    }

    public function singleArray($parentArray)
    {
        $singleArray = [];
        foreach ($parentArray as $childArray) {
            foreach ($childArray as $value) {
                $singleArray[] = $value;
            }
        }

        return $singleArray;
    }

    public function random_strings($length_of_string)
    {
        // String of all alphanumeric character
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring
        // of specified length
        return substr(str_shuffle($str_result), 0, $length_of_string);
    }

    public function generate_numbers($start, $count, $digits)
    {
        $result = array();
        for ($n = $start; $n < $start + $count; $n++) {
            $result[] = str_pad($n, $digits, "0", STR_PAD_LEFT);
        }
        return $result;
    }

    public function generate_otp($length)
    {
        $characters = '123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function removeNull($array)
    {
        array_walk_recursive($array, function (&$array, $key) {
            $array = (null === $array) ? '' : $array;
        });
        return $array;
    }

    public function arrcheck($array)
    {
        array_walk_recursive($array, function (&$array, $key) {
            $array = (null === $array) ? '' : $array;
        });
        return $array;
    }

    public function hash($string)
    {
        return hash('sha512', $string . config_item('encryption_key'));
    }

    public function enc_password($password)
    {
        $encrypted_password = password_hash($password, PASSWORD_DEFAULT);
        return $encrypted_password;
    }

    /**
     * @param $customer
     * @param $companyId
     * @return string|int
     */
    protected function getUserIdFromCustomerOrCompany($customer, $companyId)
    {
        $usuarios = $this->Apimodel->get_cond(
            'vendors',
            "company_id=" . $companyId . " AND `default` = 1"
        );
        $cadena = $customer->user_id;
        $userId=0;

        if (strpos($cadena, ',') !== false) {
            $partes = explode(',', $cadena);
            $userId = $partes[0];
        } else {
            if ($cadena == '0') {
                if (!empty($usuarios)) {
                    $userId = $usuarios->user_id;
                }
            } else {
                $userId = $cadena;
            }
        }
        return $userId;
    }

    protected function addHistory($order_id,$company_id,$user_id,$status_id): void
    {
        $dataHistoryStatusOrder = [
            "order_id" => $order_id,
            "company_id" => $company_id,
            "user_id" => $user_id,
            "status_id" => $status_id,
        ];
        $this->HistoryStatusOrderModel->add_history($dataHistoryStatusOrder);
    }

}
