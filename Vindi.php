<?php
defined('BASEPATH') OR exit('No direct script access allowed');

interface CVindi{

    public function getSubscriptionRelationship(String $plano):Int;                                      # Pega a relação do plano da vaicard com a assinatura da vindi
    public function getProductRelationship(String $cliente_contrato_produto):Int;                        # Pega a relação do produto extra da vaicard com a produto da vindi

    public function createClient(Array $data,String $route, String $method):Array;                       # Cria um cliente                       POST
    public function findClient(String $data,String $route, String $method):Array;                        # Busca cliente pro id                  GET
    // public function findClients();                                      # Busca todos os clientes               GET
    // public function updateClient(String $id);                           # Atualiza um cliente                   PUT
    // public function deleteClient(String $id);                           # Deleta um cliente                     DELETE

    public function addPaymentPerfilClient(Array $data,String $route, String $method):Array;             # Adiciona perfil de pagamento cliente  POST
    public function removePaymentPerfilClient(String $data,String $route, String $method):Array;         # Remove perfil de pagamento cliente    DELETE

    public function createNewProduct(Array $data,String $route, String $method):Array;                  # Adiciona um novo produo a vindi       POST
    public function createNewPlan(Array $data,String $route, String $method):Array;                  # Adiciona um novo produo a vindi       POST

    public function addSubscriptionClient(Array $data,String $route, String $method):Array;             # Adiciona relação assinatura cliente   POST
    public function findSubscriptionClient(String $data,String $route, String $method):Array;           # Encontra assinatura do cliente        GET
    public function removeSubscriptionClient(String $data,String $route, String $method):Array;         # Cancela assinatura                    DELETE
    // public function reactiveSubscriptionClient(String $assinatura);     # Reativa assinatura                    POST
    // public function renewSubscriptionClient(String $assinatura);        # Renova assinatura                     POST

    public function addItem(Array $data,String $route, String $method):Array;                           # Adiciona item á assinatura            POST
    public function removeItem(String $data,String $route, String $method):Array;                       # Remove item da assinatura             DELETE
    public function updateItem(Array $data,String $route, String $method):Array;                        # Atualiza item da assinatura           PUT
    // public function findItem(String $assinatura_id, String $item_id);   # encontra item da assinatura           GET

    public function addNewCharge(Array $data,String $route, String $method):Array;                      # Adiciona uma nova cobrança    POST
    public function findCharge(String $data,String $route, String $method):Array;                       # Busca uma cobrança pelo id    GET
    public function cancelCharge(String $data,String $route, String $method):Array;                     # Cancela uma cobrança pelo id  GET
    public function updateCharge(Array $data,String $route, String $method):Array;                      # Atualiza uma cobrança pelo id  PUT

    public function findCharges(String $data,String $route, String $method):Array;                      # Pega Lista de cobranças       GET

    public function findPeriods(String $data,String $route, String $method):Array;                       # Pega Lista de periodos       GET
}
/**
  * Vindi API
  *
  * Essa classe é uma integração feita com a API da VINDI
  * 
  * @subpackage libraries
  * @category   library
  * @version    0.1.6 <alpha>
  * @author     Felipe Rico Gazapina <https://github.com/FelipeGazapina>
  * @link       https://vindi.github.io/api-docs/dist/#/
  * @link       https://github.com/FelipeGazapina/VINDI
  */
  class Vindi implements CVindi{

    // private $relationshipSubscription = [
    //     13 => 65618,
    //     12 => 65991,
    //     7  => 65992,
    //     6  => 65993,
    //     4  => 65994,
    //     2  => 65995,
    //     1  => 65996,
    // ];
    // private $relationshipProduct = [
    //     6702533 => 178932,
    //     6702345 => 178930,
    //     6702346 => 178931,
    //     6703061 => 178933,
    //     6703062 => 177624,
    //     73861   => 178929,
    //     73850   => 178928,
    //     73846   => 178927,
    //     73845   => 178926,
    //     73842   => 178925,
    //     73841   => 177763,
    //     73838   => 177623,
    //     13      => 177624,
    //     12      => 178917,
    //     7       => 178918,
    //     6       => 178919,
    //     4       => 178920,
    //     2       => 178921,
    //     1       => 178922,
    //     'adesao'=> 177814,
    //     "renegociacao" => 178164,
    // ];
    // private $relationshipProductProduction = [
    //     6702533 => 178932,
    //     6702345 => 178930,
    //     6702346 => 178931,
    //     6703061 => 954000,
    //     6703062 => 177624,
    //     73861   => 178929,
    //     73850   => 954001,
    //     73846   => 178927,
    //     73845   => 178926,
    //     73842   => 178925,
    //     73841   => 177763,
    //     73838   => 953989,
    //     13      => 177624,
    //     12      => 178917,
    //     7       => 953994,
    //     6       => 954002,
    //     4       => 178920,
    //     2       => 954003,
    //     1       => 178922,
    //     'adesao'=> 953979,
    //     "renegociacao" => 178164,
    // ];

    private $production_token = $_ENV['API_TOKEN'];
    private $development_token = $_ENV['SANDBOX_TOKEN'];

    private $state = $_ENV['STATE'];
    private $url_production_api = $_ENV['PRODUCTION_URL'];
    private $url_development_api = $_ENV['DEVELOPMENT_URL'];

    public function __construct(){
        if($this->state == 'development')
            $this->production_token = NULL;
            $this->url_production_api = NULL;
    }

    /**
     * Cria novo Plano vindi
     *
     * @param Array $data ["name"=>"string","interval"=>"days","interval_count"=>0,"billing_trigger_type"=>"beginning_of_period","billing_trigger_day"=>0,"billing_cycles"=>0,"code"=>"string","description"=>"string","installments"=>0,"invoice_split"=>true,"status"=>"active","plan_items"=>[["cycles"=>0,"product_id"=>0]],"metadata"=>"metadata"]
     * @param String $route ["plans/"]
     * @param String $method ["POST"]
     * @return Array
     */
    public function createNewPlan(Array $data,String $route, String $method):Array{

        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }

        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Atualizar Plano Vindi
     *
     * @param Array $data
     * @param String $route
     * @param String $method
     * @return Array
     */
    public function updatePlan(Array $data,String $route, String $method):Array{
         # PASSANDO DE ARRAY PARA JSON
         $post = json_encode($data);
         $curl = curl_init();
 
         $curl = $this->set_post($curl,$post,$route,$method);

 
         $response = curl_exec($curl);
         $err = curl_error($curl);
 
         $resp = (array) $response;
         $resp = json_decode($resp[0]);
         curl_close($curl);
 
         if(isset($resp->errors)){
             $err = ["status"=>500,"errors"=>$resp];
         }else{
             $resp = [
                 "status"=>200,
                 "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                 "dados" => $resp
             ];
         }
         
         if ($err) {
         return $err;
         } else {
         return $resp;
         }
    }

    /**
     * Cria novo produto vindi
     *
     * @param Array $data ["name":"string","code":"string","unit":"string","status":"active","description":"string","invoice":"always","pricing_schema":["price":0,"minimum_price":0,"schema_type":"flat","pricing_ranges":[["start_quantity":0,"end_quantity":0,"price":0,"overage_price":0]]],"metadata":"metadata"]
     * @param String $route ["products/"]
     * @param String $method ["POST"]
     * @return Array
     */
    public function createNewProduct(Array $data,String $route, String $method):Array{

        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }

        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Função para criação de cliente
     * @author Felipe Rico Gazapina <https://github.com/FelipeGazapina>
     * @param data {
     *  "name": "string",
     *  "email": "string",
     *  "registry_code": "string",
     *  "code": "string",
     *  "notes": "string",
     *  "metadata": "metadata",
     *  "address": {
     *    "street": "string",
     *    "number": "string",
     *    "additional_details": "string",
     *    "zipcode": "string",
     *    "neighborhood": "string",
     *    "city": "string",
     *    "state": "string",
     *    "country": "string"
     *  },
     *  "phones": [
     *    {
     *      "phone_type": "mobile",
     *      "number": "string",
     *      "extension": "string"
     *    }
     *  ]
     *  }
     * @param String route ["customers","customers/{id}"]
     * @param String method ["POST","GET","PUT", "DELETE"]
     * 
     * 
     * @var String method
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function createClient(Array $data,String $route, String $method):Array{

        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();
        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){

            $err = ["status"=>500,"errors"=>$resp];
        }else{

            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }

        if ($err) {
        return $err;
        } else {
        return $resp;
        }

    } 

    /**
     * Encotra o cliente na VINDI
     *
     * @param String $client_id id referente a assinatura do vindi
     * @param String route ["customers/"]
     * @param String method ["GET"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function findClient(String $data, String $route, String $method):Array{
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Insersão de produtos adicionais ah uma assinatura já existente
     * DADOS OBRIGARÓRIOS DATA ["product_id"=> ,"subscription_id"=> "","quantity"=> 1]
     *
     * @param array $data 
     * @param string $route ["product_items/"]
     * @param string $method ["POST"]
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function addItem(Array $data, String $route, String $method):Array{
            
        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Atualização de produtos ah uma assinatura já existente
     *
     * @param array $data ["status"=>"active","cycles"=>0,"quantity"=>0,"pricing_schema"=>["price"=>0,"minimum_price"=>0,"schema_type"=>"flat","pricing_ranges"=>[["start_quantity"=>0,"end_quantity"=>0,"price"=>0,"overage_price"=>0]]]]
     * @param string $route ["product_items/{id}"]
     * @param string $method ["PUT"]
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function updateItem(Array $data, String $route, String $method):Array{
            
        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Remover Item da assinatura
     *
     * @param String data vinculo da assinatura id
     * @param String route ["product_items/{id}"]
     * @param String method ["DELETE"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function removeItem(String $data,String $route, String $method):Array{
        
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Este método irá criar uma assinatura e obrigatoriamente retornar o objeto subscription. 
     * Se os parâmetros do plano indicarem uma cobrança imediata, 
     * o retorno da mesma requisição também irá conter os detalhes da fatura emitida, 
     * representada pelo objeto bill. Dentro deste objeto você poderá encontrar informações 
     * mais detalhadas sobre o processamento da transação (last_transaction).
     * 
     * @author Felipe Rico Gazapina <https://github.com/FelipeGazapina>
     * @param data {
     *  "start_at": "string",
     *  "plan_id": 0,
     *  "customer_id": 0,
     *  "code": "string",
     *  "payment_method_code": "string",
     *  "installments": 0,
     *  "billing_trigger_type": "beginning_of_period",
     *  "billing_trigger_day": 0,
     *  "billing_cycles": 0,
     *  "metadata": "metadata",
     *  "product_items": [
     *    {
     *      "product_id": 0,
     *      "cycles": 0,
     *      "quantity": 0,
     *      "pricing_schema": {
     *        "price": 0,
     *        "minimum_price": 0,
     *        "schema_type": "flat",
     *        "pricing_ranges": [
     *          {
     *            "start_quantity": 0,
     *           "end_quantity": 0,
     *            "price": 0,
     *            "overage_price": 0
     *          }
     *        ]
     *      },
     *      "discounts": [
     *        {
     *          "discount_type": "percentage",
     *          "percentage": 0,
     *          "amount": 0,
     *          "quantity": 0,
     *          "cycles": 0
     *        }
     *      ]
     *    }
     *  ],
     *  "payment_profile": {
     *    "id": 0,
     *    "token": "string",
     *    "holder_name": "string",
     *    "registry_code": "string",
     *    "bank_branch": "string",
     *    "bank_account": "string",
     *    "card_expiration": "string",
     *    "allow_as_fallback": true,
     *    "card_number": "string",
     *    "card_cvv": "string",
     *    "card_token": "string",
     *    "gateway_id": "string",
     *    "payment_method_code": "string",
     *    "payment_company_code": "string",
     *    "gateway_token": "string"
     *  },
     *  "invoice_split": true
     *}
     * @param String route ["subscriptions""]
     * @param String method ["POST"]
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function addSubscriptionClient(Array $data,String $route, String $method):Array{

        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }

    } 

    /**
     * A assinatura é uma das entidades principais da plataforma e representa a relação entre um plano e um cliente.
     * É a partir dela que faturas, cobranças e períodos são gerados.
     *
     * @param String $subscription_id id referente a assinatura do vindi
     * @param String route ["subscriptions/"]
     * @param String method ["GET"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function findSubscriptionClient(String $data,String $route, String $method):Array{
     
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }
    /**
     * Remover assinatura do cliente
     *
     * @param String data $subscription_id
     * @param String route ["payment_profiles/{id}"]
     * @param String method ["DELETE"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function removeSubscriptionClient(String $data,String $route, String $method):Array{
        
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Adicionar perfil de pagamento ao cliente
     * O perfil de pagamento representa um cartão de crédito ou uma conta bancária armazenada na plataforma Vindi.
     *
     * @param Array data ["holder_name"=> "José da Silva","card_expiration"=> "12/2018","card_number"=> "5167454851671773","card_cvv"=> "123","payment_method_code"=> "credit_card","payment_company_code"=> "mastercard","customer_id"=> 51]
     * @param String route ["payment_profiles","payment_profiles/{id}"]
     * @param String method ["POST","GET","PUT","DELETE"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function addPaymentPerfilClient(Array $data,String $route, String $method):Array{
        
        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Remover perfil de pagamento ao cliente
     * O perfil de pagamento representa um cartão de crédito ou uma conta bancária armazenada na plataforma Vindi.
     *
     * @param String data $carteira_pagamento_id
     * @param String route ["payment_profiles/{id}"]
     * @param String method ["DELETE"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function removePaymentPerfilClient(String $data,String $route, String $method):Array{
        
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Faturas avulsas são independentes de assinaturas e podem ser usadas para cobrar qualquer tipo de valor não recorrente.
     * DADOS OBRIGARÓRIOS DATA ["customer_id"=> 28,"payment_method_code"=> "bank_slip","bill_items"=> [["product_id"=> 14,"amount"=> 100]]]
     *
     * @param array $data ["customer_id"=>0,"code"=>"string","installments"=>0,"payment_method_code"=>"string","billing_at"=>"string","due_at"=>"string","bill_items"=>[["product_id"=>0,"product_code"=>"string","amount"=>0,"description"=>"string","quantity"=>0,"pricing_schema"=>["price"=>0,"minimum_price"=>0,"schema_type"=>"flat","pricing_ranges"=>[["start_quantity"=>0,"end_quantity"=>0,"price"=>0,"overage_price"=>0]]]]],"metadata"=>"metadata","payment_profile"=>["id"=>0,"token"=>"string","holder_name"=>"string","registry_code"=>"string","bank_branch"=>"string","bank_account"=>"string","card_expiration"=>"string","allow_as_fallback"=>true,"card_number"=>"string","card_cvv"=>"string","card_token"=>"string","gateway_id"=>"string","payment_method_code"=>"string","payment_company_code"=>"string","gateway_token"=>"string"],"payment_condition"=>["penalty_fee_value"=>0,"penalty_fee_type"=>"string","daily_fee_value"=>0,"daily_fee_type"=>"string","after_due_days"=>0,"payment_condition_discounts"=>[["value"=>0,"value_type"=>0,"days_before_due"=> 0]]]]
     * @param string $route ["bills","bills/{id}","bills/{id}/approve"]
     * @param string $method ["POST","GET","DELETE","PUT"]
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     * @return void
     */
    public function addNewCharge(array $data, string $route, string $method):Array{
            
        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Atualiza os valores da fatura que forem mandados pela variavel data
     *
     * @param array $data ["code"=>"string","payment_method_code"=>"string","installments"=>0,"billing_at"=>"string","due_at"=>"string","metadata"=>"metadata","payment_profile"=>["id"=>0,"token"=>"string","holder_name"=>"string","registry_code"=>"string","bank_branch"=>"string","bank_account"=>"string","card_expiration"=>"string","allow_as_fallback"=>true,"card_number"=>"string","card_cvv"=>"string","card_token"=>"string","gateway_id"=>"string","payment_method_code"=>"string","payment_company_code"=>"string","gateway_token"=>"string"]]
     * @param string $route ["bills/{id}"]
     * @param string $method ["PUT"]
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     * @return void
     */
    public function updateCharge(array $data, string $route, string $method):Array{
            
        # PASSANDO DE ARRAY PARA JSON
        $post = json_encode($data);
        $curl = curl_init();

        $curl = $this->set_post($curl,$post,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Encontra a BILL respectiva de acordo com o id da cobrança vindi
     *
     * @param String $vindi_charge_id id referente a bill do vindi
     * @param String route ["bills/"]
     * @param String method ["GET"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function findCharge(String $data,String $route, String $method):Array{

        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    } 

    /**
     * Encontra a BILL respectiva de acordo com o id da cobrança vindi
     *
     * @param String $vindi_charge_id id referente a bill do vindi
     * @param String route ["bills/"]
     * @param String method ["GET"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function cancelCharge(String $data,String $route, String $method):Array{

        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    } 

    /**
     * Encotra Cobranças do cliente
     *
     * @param String $query filtro de busca
     * @param String route ["charges/"]
     * @param String method ["GET"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function findCharges(String $data, String $route, String $method):Array{
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Encotra Periodos registrados na assinatura do cliente
     *
     * @param String $query filtro de busca
     * @param String route ["periods/"]
     * @param String method ["GET"]
     * 
     * 
     * @return Array ["status"=> code, "message"=> "mensagem descritiva status", "dados"=> "array[dados retornado da API]"]
     */
    public function findPeriods(String $data, String $route, String $method):Array{
        
        $curl = curl_init();

        $curl = $this->set_get($curl,$data,$route,$method);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $resp = (array) $response;
        $resp = json_decode($resp[0]);
        curl_close($curl);

        if(isset($resp->errors)){
            $err = ["status"=>500,"errors"=>$resp];
        }else{
            $resp = [
                "status"=>200,
                "message"=> "Seu pedido foi realizado com sucesso. (Verifique seu email)",
                "dados" => $resp
            ];
        }
        
        if ($err) {
        return $err;
        } else {
        return $resp;
        }
    }

    /**
     * Função que seta as confirgurações do CURL para method POST | PUT
     *
     * @param [Curl] $curl Próprio CURL
     * @param String $post dados a serem passados através do post
     * @param String $route rota que será enviado
     * @param String $method método que será usado
     * @return $curl \CurlHandle
     */
    private function set_post($curl,String $post,String $route, String $method){
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url_development_api . $route,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->development_token . ":",        
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json; charset=utf-8",
                // "Authorization: Basic $token"
            ],
        ]);
        return $curl;
    }

    /**
     * Função que seta as confirgurações do CURL para method GET | DELETE
     *
     * @param [Curl] $curl Próprio CURL
     * @param String $data dados a serem passados através do get
     * @param String $route rota que será enviado
     * @param String $method método que será usado
     * @return $curl \CurlHandle
     */
    private function set_get($curl,String $data,String $route, String $method){
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url_development_api . $route . $data,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->development_token . ":",        
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json; charset=utf-8",
                // "Authorization: Basic $token"
            ],
        ]);
        return $curl;

    }

    /**
     * Função para pegar relacionamento de 
     * plano vaicard com assinatura vindi
     * @param String plano nº plano vaicard
     * @return Int assinatura nº assinatura vindi
     */
    public function getSubscriptionRelationship(String $plano):Int{
        if(array_key_exists($plano,$this->relationshipProduct)){
            return $this->relationshipSubscription[$plano];
        }
		$CI =& get_instance();
        $result = $CI->planos->primeiro($plano);
    
        if(!empty($result->vindi_plan_id)){
            return $result->vindi_plan_id;
        }
        return 0;
    }

    /**
     * Função para pegar relacionamento de 
     * cliente_contrato_produto vaicard com produto vindi
     * @param String cliente_contrato_produto nº cliente_contrato_produto vaicard
     * @return Int produto nº produto vindi
     */
    public function getProductRelationship(String $cliente_contrato_produto):Int{
        if(array_key_exists($cliente_contrato_produto,$this->relationshipProduct)){
            return $this->relationshipProduct[$cliente_contrato_produto];
        }

		$CI =& get_instance();
        $result = $CI->produtos->primeiro($cliente_contrato_produto);
        
        if(!empty($result->vindi_id_product)){
            return $result->vindi_id_product;
        }
        return 0;
    }

  } 