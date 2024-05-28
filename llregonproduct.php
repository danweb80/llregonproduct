<?php

/*
 * Plugin Name: LeadLovers - Cadastro no Produto
 * Description: Cadastra automaticamente um cliente que acabou de comprar seu Produto pelo Woocommerce na plataforma de Cursos e na Máquina do LeadLovers.
 * Author: Daniel Weber 
 * Author URI: mailto://prof.daniel.weber@gmail.com
 * Version: 2.2
 * License: GPLv2 or later
 */

 //Prefix/slug - llregonproduct

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//******************************************************************************
//******************************************************************************
//				 PÁGINA DE CONFIGURAÇÕES DO WOOCOMMERCE
//******************************************************************************
//******************************************************************************

// ALGUNS LINKS QUE ME AJUDARAM A CONSTRUIR A PÁGINA DE CONFIGURAÇÕES
//Principalmente>>>> https://wordpress.stackexchange.com/questions/407390/add-a-custom-woocommerce-settings-tab-with-sections
//https://stackoverflow.com/questions/72816886/add-a-custom-woocommerce-settings-page-including-page-sections
//https://gist.github.com/renventura/ff93a87f8779457b72ff4dc0366ba053/revisions
//https://felipeelia.com.br/como-criar-uma-tela-de-configuracao-para-o-seu-plugin-wordpress-com-a-settings-api-parte-2/
//https://www.tychesoftwares.com/how-to-add-custom-sections-fields-in-woocommerce-settings/
//https://www.speakinginbytes.com/2014/07/woocommerce-settings-tab/
//https://www.tychesoftwares.com/how-to-add-custom-sections-fields-in-woocommerce-settings/
//types https://github.com/woocommerce/woocommerce/blob/fb8d959c587ee95f543e682e065192553b3cc7ec/includes/admin/class-wc-admin-settings.php#L246

//******************************************************************************
//** Cria a tab "LeadLovers" na página de configurações do Woocommerce
//******************************************************************************

add_filter( 'woocommerce_settings_tabs_array', 'add_leadlovers_tab', 50 ); 
function add_leadlovers_tab( $settings_tabs ) {
    $settings_tabs['leadlovers'] = __( 'LeadLovers', 'woocommerce' );

    return $settings_tabs;
}

//******************************************************************************
//** Adiciona uma section à tab "LeadLovers"
//******************************************************************************
add_action( 'woocommerce_sections_leadlovers', 'action_woocommerce_sections_leadlovers', 10 );
function action_woocommerce_sections_leadlovers() {
    global $current_section;
    $tab_id = 'leadlovers';
    // Must contain more than one section to display the links
    // Make first element's key empty ('')
    $sections = array(
        ''   => __( '', 'woocommerce' ),
        //'produtos'   => __( 'Produtos', 'woocommerce' ),
    );
    echo '<ul class="subsubsub">';
    $array_keys = array_keys( $sections );
    foreach ( $sections as $id => $label ) {
        echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $tab_id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
    }
    echo '</ul><br class="clear" />';
}

//******************************************************************************
//** Adiciona as configurações 
//******************************************************************************
add_action( 'woocommerce_settings_leadlovers', 'action_woocommerce_settings_leadlovers', 10 );
function action_woocommerce_settings_leadlovers() {
    // Call settings function
    $settings = get_leadlovers_settings();
    WC_Admin_Settings::output_fields( $settings );  
}

//**************************************************************************************
//** Salva as configurações
//**************************************************************************************
add_action( 'woocommerce_settings_save_leadlovers', 'action_woocommerce_settings_save_leadlovers', 10 );
function action_woocommerce_settings_save_leadlovers() {
    global $current_section;
    $tab_id = 'leadlovers';
    // Call settings function
    $settings = get_leadlovers_settings();
    WC_Admin_Settings::save_fields( $settings );
    if ( $current_section ) {
        do_action( 'woocommerce_update_options_' . $tab_id . '_' . $current_section );
    }
}

//**************************************************************************************
//** Cria as páginas de configurações na tab LeadLovers do Woocommerce
//**************************************************************************************
add_action( 'woocommerce_settings_leadlovers', 'get_leadlovers_settings', 10 );
function get_leadlovers_settings()
{
	// Checa se estamos na seção leadlovers
	$settings_leadlovers = array();
	// Título
	$settings_leadlovers[] = array( 
		'name' => __( 'Integração com a plataforma de cursos da LeadLovers', 'woocommerce' ),
		'type' => 'title',
		'desc' => __( 'Configurações para o cadastro automático dos clientes com a plataforma de Cursos LeadLovers após uam compra bem sucedida.<br>O SKU dos produtos woocommerce deve ser equivalente ao PdocutId dos produtos LeadLovers', 'woocommerce' ),
		'id' => 'wc_leadlovers_title'
	);
	// Token
	$settings_leadlovers[] = array(
		'name'     => __( 'TOKEN', 'woocommerce' ),
		'id'       => 'wc_leadlovers_token',
		'type'     => 'text',
		'desc'     => __( 'Insira aqui o token pessoal fornecido pela LeadLovers nas <a href="https://app.leadlovers.com/settings">configurações do usuário</a>!', 'woocommerce' ),
	);
	// Trigger
	$settings_leadlovers[] = array(
		'name'     => __( 'Trigger do pedido', 'woocommerce' ),
		'desc_tip'      => __( 'O cadastro será efetuado quando o status for este. Caso seja "Processsando" mudará para "Concluído" automaticamente se o cadastro for efetuado ou o cliente já existir no produto', 'woocommerce' ),
		'id'        => 'wc_leadlovers_order_status_trigger',
		'class'     => 'wc-enhanced-select',
		'default'   => 'completed',
		'type'      => 'select',
		'options'   => array(
			'completed'     => __( 'Concluído', 'woocommerce' ),
			'processing'    => __( 'Processando', 'woocommerce' ),
		),
		//   'desc_tip' => true,
	);
	// Checkbox de notiicação de erro
	$settings_leadlovers[] = array(
		'name'     => __( 'Notificação de erro', 'woocommerce' ),
		'id'       => 'wc_leadlovers_error_notification',
		'type'     => 'checkbox',
		'desc'     => __( 'Receber notificação de erro por e-mail', 'woocommerce' ),
	);		
	// Checkbox de notificação de sucesso
	$settings_leadlovers[] = array(
		'name'     => __( 'Notificação de sucesso', 'woocommerce' ),
		'id'       => 'wc_leadlovers_success_notification',
		'type'     => 'checkbox',
		'desc'     => __( 'Receber notificação de sucesso por e-mail', 'woocommerce' ),
	);		
	// E-mail da notificação
	$settings_leadlovers[] = array(
		'name'     => __( 'E-mail de notificação', 'woocommerce' ),
		'id'       => 'wc_leadlovers_debug_email',
		'type'     => 'text',
	);

	$settings_leadlovers[] = array( 'type' => 'sectionend', 'id' => 'leadlovers' );
	return $settings_leadlovers;
}



//******************************************************************************
//******************************************************************************
//	PÁGINA DE EDIÇÃO DE PRODUTOS
//******************************************************************************
//******************************************************************************

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
// Adiciona uma nova guia às guias de opçoes de produto (product_data_tabs)
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_filter( 'woocommerce_product_data_tabs', 'adicionar_guia_leadlovers' );
function adicionar_guia_leadlovers($tabs) {
    $tabs['guia_leadlovers'] = array(
        'label'    => __( 'LeadLovers', 'text-domain' ),
        'target'   => 'leadlovers_product_data',
        'class'    => array( 'show_if_simple', 'show_if_variable' ),
		// 'callback' => 'add_leadlovers_custom_fields'
    );
    return $tabs;
}

// Artigos que me ajudaram:
// Excelente artigo https://kb.iwwa.com.br/como-criar-campos-personalizados-para-produtos-woocommerce/
// https://stackoverflow.com/questions/45911162/woocommerce-wp-select-options-array-from-product-attribute-terms
// https://stackoverflow.com/questions/43698813/in-woocommerce-product-options-edit-page-display-a-custom-field-before-the-sku

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_filter('woocommerce_product_data_panels','add_leadlovers_custom_fields' );
function add_leadlovers_custom_fields()
{
	global $woocommerce, $post, $product_object;
 
	echo '<div id="leadlovers_product_data" class="panel woocommerce_options_panel">
    	  <div class="options_group">';
	
		  echo '<p>Opções de cadastro no LeadLovers para o produto "<strong>'.$product_object->get_name().'</strong>":</p>';

	
	// Opções de cadastro no Produto

	$leadlovers_check = $post->_leadlovers_integration_check ? 'yes' : 'no';
	woocommerce_wp_checkbox( 
		array(
			'id'      		=> '_leadlovers_integration_check',
			'value'			=> $leadlovers_check,
			'label'   		=> __( 'Integrar a um Produto LeadLovers?', 'woocommerce' ),
			'wrapper_class' => 'show_if_simple',
			'description'   => __( 'Neste caso, o SKU será definido automaticamente a partir da integração com o Produto LeadLovers.', 'woocommerce' ),
			'desc_tip' 		=> true
		)
	);
	
	echo '<div class=" leadlovers_integration_options">';
	// caso o checkbox "Integrar ao LeadLovers?" não esteja marcado
	// para mostrar/esconder a div temos uma função js em /js/llregonproduct.js
	if ($leadlovers_check != 'yes')
	{
		// 	esconde a div
		echo '<style>.leadlovers_integration_options{display:none;}</style>';
	}
	
	$leadlovers_product_options = get_leadlovers_product_options();
	woocommerce_wp_select( 
		array(
			'id'    	=> '_leadlovers_integration_product',
			'label' 	=> __( 'Produto LeadLovers', 'woocommerce' ),
			'value'   	=> $post->_leadlovers_integration_product,
			'options' 	=> $leadlovers_product_options,
			'description' => 'Selecione o produto.',
			'desc_tip' 	=> true
		)
	);

	echo '</div>';


	// Opções de cadastro na Máquina

	$leadlovers_machine_check = $post->_leadlovers_machine_check ? 'yes' : 'no';
	woocommerce_wp_checkbox( 
		array(
			'id'      		=> '_leadlovers_machine_check',
			'value'			=> $leadlovers_machine_check,
			'label'   		=> __( 'Integrar a uma Máquina LeadLovers?', 'woocommerce' ),
			'wrapper_class' => 'show_if_simple',
			'description'   => __( 'Assinale para permitir o cadastro do email  do cliente a uma máquina da LeadLovers.', 'woocommerce' ),
			'desc_tip' 		=> true
		)
	);

	echo '<div class=" leadlovers_machine_options">';
	// caso o checkbox "Integrar ao LeadLovers?" não esteja marcado
	// para mostrar/esconder a div temos uma função js em /js/llregonproduct.js
	if ($leadlovers_check != 'yes')
	{
		// 	esconde a div
		echo '<style>.leadlovers_machine_options{display:none;}</style>';
	}

	$leadlovers_machine_options = get_leadlovers_machine_options();
	
	woocommerce_wp_select( 
		array(
			'id'    	=> '_leadlovers_integration_machine',
			'label' 	=> __( 'Máquina', 'woocommerce' ),
			'value'   	=> $post->_leadlovers_integration_machine,
			'options' 	=> $leadlovers_machine_options,
			'description' => 'Selecione uma tag LeadLovers a ser aplicada ao ao cliente que adquiriu o produto.',
			'desc_tip' 		=> true
		)
	);

	$leadlovers_sequence_options = get_leadlovers_sequence_options();
	
	woocommerce_wp_select( 
		array(
			'id'    	=> '_leadlovers_integration_sequence',
			'label' 	=> __( 'Sequência', 'woocommerce' ),
			'value'   	=> $post->_leadlovers_integration_sequence,
			'options' 	=> $leadlovers_sequence_options,
			'description' => 'Selecione uma tag LeadLovers a ser aplicada ao ao cliente que adquiriu o produto.',
			'desc_tip' 		=> true
		)
	);

	 $leadlovers_level_options = get_leadlovers_level_options();
	
	woocommerce_wp_select( 
		array(
			'id'    	=> '_leadlovers_integration_level',
			'label' 	=> __( 'Nível', 'woocommerce' ),
			'value'   	=> $post->_leadlovers_integration_level,
			'options' 	=> $leadlovers_level_options,
			'description' => 'Selecione uma tag LeadLovers a ser aplicada ao ao cliente que adquiriu o produto.',
			'desc_tip' 		=> true
		)
	);

	$leadlovers_tag_options = get_leadlovers_tag_options();
	
	woocommerce_wp_select( 
		array(
			'id'    	=> '_leadlovers_integration_tag',
			'label' 	=> __( 'Tag LeadLovers', 'woocommerce' ),
			'value'   	=> $post->_leadlovers_integration_tag,
			'options' 	=> $leadlovers_tag_options,
			'description' => 'Selecione uma tag LeadLovers a ser aplicada ao ao cliente que adquiriu o produto.',
			'desc_tip' 	=> true
		)
	);
	
	echo '</div>';
	echo '</div>';
	echo '</div>';

}

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** SALVA OS ITENS NO PRODUCT POST
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_action( 'woocommerce_process_product_meta', 'save_leadlovers_custom_fields', 50);
function save_leadlovers_custom_fields( $post_id )
{
	update_post_meta( $post_id, '_leadlovers_integration_check', esc_attr( $_POST['_leadlovers_integration_check'] ) );
	update_post_meta( $post_id, '_leadlovers_machine_check', esc_attr( $_POST['_leadlovers_machine_check'] ) );
	update_post_meta( $post_id, '_leadlovers_integration_product', esc_attr( $_POST['_leadlovers_integration_product'] ) );
	update_post_meta( $post_id, '_leadlovers_integration_machine', esc_attr( $_POST['_leadlovers_integration_machine'] ) );
	update_post_meta( $post_id, '_leadlovers_integration_sequence', esc_attr( $_POST['_leadlovers_integration_sequence'] ) );
	update_post_meta( $post_id, '_leadlovers_integration_level', esc_attr( $_POST['_leadlovers_integration_level'] ) );
	update_post_meta( $post_id, '_leadlovers_integration_tag', esc_attr( $_POST['_leadlovers_integration_tag'] ) );
}

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** CARREGA O SCRIPT JS PARA OS PARANAUÊS HIDE/SHOW NAS PÁGINAS ADMIN
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_action('admin_enqueue_scripts', 'import_leadlovers_js_files');
function import_leadlovers_js_files() 
{
	// Artigos / tutoriais sobre JS no WP
	// https://www.youtube.com/watch?v=A97tmGrMkYA

	//Arquivos JS carregam só na página de edicação de produtos
	if (get_current_screen()->post_type == 'product')
	{
		//JS responsável pelo botão e mostra/esconde do checkbox
		wp_enqueue_script( 'llreongonproduct', '/wp-content/plugins/llregonproduct/js/llregonproduct.js', array() , date("h:I:s"));
		wp_localize_script( 'llreongonproduct', 'ajax_leadlovers_object', array('url' => admin_url('admin-ajax.php')) );
	}
}

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
//** NUM ARRAY ASSOCIATIVO [ProductId] => [ProductName]
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
function get_leadlovers_product_options()
{
	// artigos que ajudam
	// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
	
	$url = 'http://llapi.leadlovers.com/webapi';
		
	// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
	// cadastrado nas configurações do Woocommerce na aba Leadlovers
	$token = get_option('wc_leadlovers_token');  
	//envia a requisição de cadastro (/customer)

	$response = wp_remote_get( $url . '/products' . '?token=' . $token, array(
		'method'      => 'GET',
		'timeout'     => 30,
		'redirection' => 10,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array('accept: application/json'),
	));

	$status = wp_remote_retrieve_response_code($response);
	if ( 200 == $status ) 
	{
		// pegamos o "corpo" da resposta recebida...
		$response_body = wp_remote_retrieve_body( $response );
		// e transformamos de JSON em um array PHP normal.
		$response_data = json_decode( $response_body, true );
		
		$response_sku_list = array();
		$items = $response_data['Items'];
		foreach ( $items as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$response_sku_list += array($item['ProductId'] => $item['ProductName'] . ' (' . $item['ProductId'] . ')' );
		}	
		
		return $response_sku_list;
	}
}


///-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
//** NUM ARRAY ASSOCIATIVO [ProductId] => [ProductName]
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
function get_leadlovers_machine_options()
{
	// artigos que ajudam
	// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
	
	global $woocommerce, $post;

	$url = 'http://llapi.leadlovers.com/webapi';
		
	// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
	// cadastrado nas configurações do Woocommerce na aba Leadlovers
	$token = get_option('wc_leadlovers_token');  
	//envia a requisição de cadastro (/customer)

	$response = wp_remote_get( $url . '/machines' . '?token=' . $token, array(
		'method'      => 'GET',
		'timeout'     => 30,
		'redirection' => 10,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array('accept: application/json'),
	));

	$status = wp_remote_retrieve_response_code($response);
	if ( 200 == $status ) 
	{
		// pegamos o "corpo" da resposta recebida...
		$response_body = wp_remote_retrieve_body( $response );
		// e transformamos de JSON em um array PHP normal.
		$response_data = json_decode( $response_body, true );
		//capturamos as tags da resposta
		$items = $response_data['Items'];
		//Verificamos item por item até achar o ID da TAG que cadastrada
		$machine_list = array();

		if(!$post->_leadlovers_integration_machine)
		{
			$machine_list += array( '' => 'Escolha uma Máquina' );
		}

		foreach ( $items as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$machine_list += array($item['MachineCode'] => $item['MachineName'] . ' (' . $item['MachineCode'] . ')');
		}	

		return $machine_list;
	}
	else
	{
		$machine_list[] += array('' => 'Erro ao carregar a lista de Máquinas!!!');
		return $machine_list;
	}
}

///-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
//** NUM ARRAY ASSOCIATIVO [ProductId] => [ProductName]
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
function get_leadlovers_sequence_options($machine_id = '')
{
	// artigos que ajudam
	// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
	
	global $woocommerce, $post;


	if(!$machine_id)
	{
		$machine_id = $post->_leadlovers_integration_machine;

		if(!$machine_id)
		{
			return array('' => '------- Escolha antes uma Máquina -------');
		}
	}
	
	$url = 'http://llapi.leadlovers.com/webapi';
		
	// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
	// cadastrado nas configurações do Woocommerce na aba Leadlovers
	$token = get_option('wc_leadlovers_token');  
	//envia a requisição de cadastro (/customer)

	$response = wp_remote_get( $url . '/EmailSequences' . '?token=' . $token . '&machineCode=' . $machine_id, array(
		'method'      => 'GET',
		'timeout'     => 30,
		'redirection' => 10,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array('accept: application/json'),
	));

	$status = wp_remote_retrieve_response_code($response);
	if ( 200 == $status ) 
	{
		// pegamos o "corpo" da resposta recebida...
		$response_body = wp_remote_retrieve_body( $response );
		// e transformamos de JSON em um array PHP normal.
		$response_data = json_decode( $response_body, true );
		//capturamos as tags da resposta
		$items = $response_data['Items'];
		//Verificamos item por item até achar o ID da TAG que cadastrada
		$sequence_list = array();

		foreach ( $items as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$sequence_list += array($item['SequenceCode'] => $item['SequenceName']);
		}	
	}
	else
	{
		return array('' => 'Erro ao carregar a lista de Sequências...');
	}

	return $sequence_list;
}

//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** PEGA A LISTA DE OPÇÕES E DEVOLVE AO AJAX NA FORMA HTML
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_action( 'wp_ajax_get_leadlovers_sequence_list', 'get_leadlovers_sequence_list_ajax_callback');
// add_action( 'wp_ajax_nopriv_get_leadlovers_sequence_list', 'get_leadlovers_sequence_list_ajax_callback');
function get_leadlovers_sequence_list_ajax_callback()
{
	$machine_id = wp_strip_all_tags($_POST['machine_id']);
	$response = get_leadlovers_sequence_options($machine_id);

		
	$sequence_list_html = '<option value="">--- Escolha uma Sequência ---</option>';
	foreach ( $response as $id => $label)
	{
		// salva a lista num array Id => Nome(Id) para melhor identificação
		$sequence_list_html .= '<option value="' . $id . '">' . $label . '</option>';
	}	

	wp_send_json($sequence_list_html);
}


//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
//** NUM ARRAY ASSOCIATIVO [value] => [label]
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
function get_leadlovers_level_options($sequence_id = '', $machine_id = '')
{

	global $woocommerce, $post;

	if(!$sequence_id) 
	{
		// Passar por aqui significa que veio da classe e não do ajax
		// pois o ajax passa parametro, então: verifica se na classe já há
		// salvo algum valor
		$sequence_id = $post->_leadlovers_integration_sequence;

		if(!$sequence_id)
		{
			// se nao tem nehm valor salvo retor um símbolo de bloqueio,
			// pois vai ter que esperar o usuario clicar primeiro na sequencia
			return array('' => '------- Escolha antes uma Sequência -------');
		}

		// então, uma vez que tem uma sequencia defeinida, 
		// significa que há uma máquina tb definida,
		// basta pega-la
		if(!$machine_id)
		{
			$machine_id = $post->_leadlovers_integration_machine;
		}
		
	}
	
	// $sequence_id = strval($sequence_id); // passa para inteiro
	// $machine_id = strval($machine_id); // passa para inteiro

	$url = 'http://llapi.leadlovers.com/webapi';
	// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
	// cadastrado nas configurações do Woocommerce na aba Leadlovers
	$token = get_option('wc_leadlovers_token');  

	//envia a requisição de cadastro (/customer)
	$response = wp_remote_get( $url . '/levels' .
		'?token=' . $token .
		'&machineCode=' . $machine_id . 
		'&sequenceCode=' . $sequence_id, 
		array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array('accept: application/json'),
	));

	$status = wp_remote_retrieve_response_code($response);
	if ( 200 == $status ) 
	{
		// pegamos o "corpo" da resposta recebida...
		$response_body = wp_remote_retrieve_body( $response );
		// e transformamos de JSON em um array PHP normal.
		$response_data = json_decode( $response_body, true );

		$items = $response_data['Items'];
		
		$level_list = array();

		foreach ( $items as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$level_list += array($item['Sequence'] => $item['Subject']);
		}	
	}
	else
	{
		$level_list =  array('' => 'Erro ao carregar a lista de Sequências...');
	}

	return $level_list;
}


//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** PEGA A LISTA DE OPÇÕES E DEVOLVE AO AJAX NA FORMA HTML
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
add_action( 'wp_ajax_get_leadlovers_level_list', 'get_leadlovers_level_list_ajax_callback');
function get_leadlovers_level_list_ajax_callback()
{
	$response = get_leadlovers_level_options($_POST['sequence_id'],$_POST['machine_id']);

	$level_list_html = '<option value="">--- Escolha um Nível ---</option>';

	foreach ( $response as $id => $label)
	{
		// salva a lista num array Id => Nome(Id) para melhor identificação
		$level_list_html .= '<option value="' . $id . '">' . $label . '</option>';
	}	

	wp_send_json( $level_list_html );

}


//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
//** NUM ARRAY ASSOCIATIVO [ProductId] => [ProductName]
//-==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==--==-
function get_leadlovers_tag_options()
{
	// artigos que ajudam
	// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
	
	global $woocommerce, $post;

	$url = 'http://llapi.leadlovers.com/webapi';
		
	// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
	// cadastrado nas configurações do Woocommerce na aba Leadlovers
	$token = get_option('wc_leadlovers_token');  
	//envia a requisição de cadastro (/customer)

	$response = wp_remote_get( $url . '/tags' . '?token=' . $token, array(
		'method'      => 'GET',
		'timeout'     => 30,
		'redirection' => 10,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array('accept: application/json'),
	));

	$status = wp_remote_retrieve_response_code($response);
	if ( 200 == $status ) 
	{
		// pegamos o "corpo" da resposta recebida...
		$response_body = wp_remote_retrieve_body( $response );
		// e transformamos de JSON em um array PHP normal.
		$response_data = json_decode( $response_body, true);
		//capturamos as tags da resposta
		$items = $response_data['Tags'];
		//Verificamos item por item até achar o ID da TAG que cadastrada
		$tag_list = array();

		if(!$post->_leadlovers_integration_tag)
		{
			$tag_list += array( '' => 'Escolha uma Tag' );
		}

		foreach ( $items as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$tag_list += array( $item['Id'] => $item['Title'] );
		}	
	}
	else
	{
		$tag_list[] += array('' => 'Erro ao carregar a lista de Tags...');
		// $messages .= 'Erro ao capturar a lista de tags! Erro ' . $response_data['StatusCode'] . ': ' . $response_data['Message'] . '<br>';
		// $error = true;
	}

	return $tag_list;

}



//******************************************************************************
//******************************************************************************
//				 CADASTRO DE CLIENTES NO LEADLOVERS 
//******************************************************************************
//******************************************************************************
//		usando HOOK de mudança de Status do Pedido para 
//		PROCESSANDO ou CONCLUIDO (quando um pagamento é concluído,
//		o pedido sai do status AGUARADANDO para PROCESSANDO)
//		então, automaticamente o cliente é cadadstrado na plataforma
//		LeadLovers através de um chamado HTTP POST 
//******************************************************************************
//******************************************************************************

//add_action('woocommerce_order_status_completed', 'woocommerce_to_LeadLovers_customer_register_on_product', 10, 1); 
add_action('woocommerce_order_status_changed', 'woocommerce_to_LeadLovers_customer_register_on_product', 10, 3);
function woocommerce_to_LeadLovers_customer_register_on_product($order_id, $old_status, $new_status)
{
	//testa se o trigger definido nas configurações e o status do pedido são os mesmos
	if($new_status == get_option('wc_leadlovers_order_status_trigger'))
	{
		//----------------------------------
		//  SALVA OS DADOS DO PEDIDO
		//----------------------------------
		$order = wc_get_order($order_id);
		// Tratamento dos dados do cliente extraídos do pedido (order_id)
		$customer_email = $order->get_billing_email();
		$customer_name = $order->get_formatted_billing_full_name();
		//$customer_cpf (não sei pegar o cpf, pois é um dado criado no plugin do Claudio Sanches)
		$customer_phone = $order->get_billing_phone();
		$items = $order->get_items();
		foreach ( $items as $item )
		{
			$customer_product = $item->get_product();
			$customer_product_id = $customer_product->get_meta('_leadlovers_integration_product');
			$customer_product_name = $customer_product->get_name();
			
			// Pode-se acessá-lo por uma requisição GET (ver https://llapi.leadlovers.com/Products)
			// ou na url do produto. Ex: https://app.leadlovers.com/products/details/36077 <<== Esse código!!

			//verifica se o produto está integrado à LeadLovers
			// if('yes' == get_post_meta( $customer_product->get_id(), '_leadlovers_integration_check', true ))
			if('yes' == $customer_product->get_meta('_leadlovers_integration_check'))
			{
				// ----------------------------------------------------
				// CADASTRAR NO PRODUTO
				// ----------------------------------------------------
				
				//----- leadLovers API Documentation (https://llapi.leadlovers.com/#operation/Customer_Post)
				//----- wp_remote_post Documentation https://developer.wordpress.org/reference/functions/wp_remote_post/	
				$url = 'http://llapi.leadlovers.com/webapi';
				// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
				$token = get_option('wc_leadlovers_token');  
				//envia a requisição de cadastro (/customer)
				$response = wp_remote_post( $url . '/customer' . '?token=' . $token, array(
					'method'      => 'POST',
					'timeout'     => 30,
					'redirection' => 10,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('accept: application/json'),
					'body'        => array(
						'Name' 		  => $customer_name,
						'Email'       => $customer_email,
						//'Phone' => $customer_phone,  O telefone tem que ser sanitizado para o formato zap. Ex:'5516999999999'
						//'Document' => $customer_cpf, //Ainda não consigo ler esse dado
						'ProductId' => intval($customer_product_id)
					))
				);

				// pegamos o "corpo" da resposta recebida...
				$response_body = wp_remote_retrieve_body( $response );
				// e transformamos de JSON em um array PHP normal.
				$response_data = json_decode( $response_body, true );

				//envia uma resposta nas notas do pedido 
				$order->add_order_note('LeadLovers (' . $customer_product_name . '): ' . $response_data['Message']);

				//Cuidamos de cada caso de resposta
				switch ($response_data['StatusCode'])
				{
					case 200:		//OK
						//Salva mensagem de sucesso
						$success_notification_message .= 'Cadastro no Produto... OK: ' . $response_data['Message'] . '<br>';
								
						//caso o trigger seja no status "Processando" muda automativamente para "Concluído"
						if( get_option('wc_leadlovers_order_status_trigger') == 'processing' )
						{
							$order->update_status( 'completed' );
						}
						
						break;

					case 400:		//bad request
						if( $response_data['Message'] == 'Esse cliente já existe no produto')
						{
							//caso o trigger seja no status "Processando" muda automativamente para "Concluído"
							if( get_option('wc_leadlovers_order_status_trigger') == 'processing' )
							{
								$order->update_status( 'completed' );
							}
						}
						
					case 401:		//unauthorized
					default:		// Algum erro estrutural na mensagem...
						//Salva mensagem de erro
						$error_notification_message .= 'Cadastro no Produto... Erro ' . $response_data['StatusCode'] . ': ' . $response_data['Message'] . '<br>';
				}
			}
			
			if('yes' == $customer_product->get_meta('_leadlovers_machine_check'))
			{
				// ----------------------------------------------------
				// CADASTRAR NA MÁQUINA
				// ----------------------------------------------------

				// variáveis com dados para a requisição
				$machine_id = $customer_product->get_meta('_leadlovers_integration_machine');
				$sequence_id = $customer_product->get_meta('_leadlovers_integration_sequence');
				$level_id = $customer_product->get_meta('_leadlovers_integration_level');
				$tag_id = $customer_product->get_meta('_leadlovers_integration_tag');
				
				$url = 'http://llapi.leadlovers.com/webapi';
				// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
				$token = get_option('wc_leadlovers_token');  
				//envia a requisição de cadastro (/customer)
				$response = wp_remote_post( $url . '/lead' . '?token=' . $token, array(
					'method'      => 'POST',
					'timeout'     => 30,
					'redirection' => 10,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('accept: application/json'),
					'body'        => array(
						'Name' => $customer_name,
						'Email' => $customer_email,
						'MachineCode' => intval($machine_id),
						'EmailSequenceCode' => intval($sequence_id),
						'SequenceLevelCode' => intval($level_id),
						'Tag' => intval($tag_id),
					)
				));
	
				// pegamos o "corpo" da resposta recebida...
				$response_body = wp_remote_retrieve_body( $response );
				// e transformamos de JSON em um array PHP normal.
				$response_data = json_decode( $response_body, true );

				//envia uma resposta nas notas do pedido 
				$order->add_order_note('LeadLovers Máquina(' . $machine_id . '): ' . $response_data['Message']);

				switch($response_data['StatusCode'])
				{
					case 200:	// OK!
						$success_notification_message .= 'Cadastro na Máquina... OK: '. $response_data['Message'] . '.<br>';
						break;
					case 400:
					case 401:
					default:
						$error_notification_message .= 'Cadastro na Máquina... Erro ' . $response_data['StatusCode'] . ': ' . $response_data['Message'] . '<br>';

				}
			}
			// Se  houver mensagem de sucesso ou erro, significa que este produto tem integração nenhuma com o LeadLovers,
			// portanto, cerfica-se se deve enviar conforme as config gerais do plugin, caso contrário encerra o processo
			if ($success_notification_message || $error_notification_message)
			{
				// ----------------------------------------------------
				// PROCESSA NOTIFICAÇÕES
				// ----------------------------------------------------
				$send_success = get_option('wc_leadlovers_success_notification');
				$send_error = get_option('wc_leadlovers_error_notification');
				if($send_success == 'yes' && $send_error == 'yes')
				{
					$subject = 'Notificação LeadLovers';

					$body .= $success_notification_message;
					$body .= $error_notification_message;
					$body .= "----------------------------------------------------------------" . '<br>'. 
					"Nome: " . $customer_name . '<br>' . 
					"E-mail: " . $customer_email . '<br>' . 
					"Telefone: " . $customer_phone . '<br>' . 
					"Produto: " . $customer_product_name . ' (' . $customer_product_id . ')' . '<br>'.
					"Pedido: " . $order_id . '<br>';
					//seleciona o e-mail cadastrado na página de configurações do plugin
					$to = get_option( 'wc_leadlovers_debug_email' );
					//definição padrão de cabeçalho do e-mail
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					//envia o e-mail
					wp_mail( $to, $subject, $body, $headers );
				}
				else if($send_success == 'yes' && $success_notification_message)
				{
					$subject = 'Notificação LeadLovers: Cadastro OK!';

					$body .= $success_notification_message;
					$body .= "----------------------------------------------------------------" . '<br>'. 
					"Nome: " . $customer_name . '<br>' . 
					"E-mail: " . $customer_email . '<br>' . 
					"Telefone: " . $customer_phone . '<br>' . 
					"Produto: " . $customer_product_name . ' (' . $customer_product_id . ')' . '<br>'.
					"Pedido: " . $order_id . '<br>';
					
					//seleciona o e-mail cadastrado na página de configurações do plugin
					$to = get_option( 'wc_leadlovers_debug_email' );
					//definição padrão de cabeçalho do e-mail
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					//envia o e-mail
					wp_mail( $to, $subject, $body, $headers );
				}
				else if($send_error == 'yes' && $error_notification_message)
				{
					$subject = 'Notificação LeadLovers: Cadastro com Erro!';

					$body .= $error_notification_message;
					$body .= "----------------------------------------------------------------" . '<br>'. 
					"Nome: " . $customer_name . '<br>' . 
					"E-mail: " . $customer_email . '<br>' . 
					"Telefone: " . $customer_phone . '<br>' . 
					"Produto: " . $customer_product_name . ' (' . $customer_product_id . ')' . '<br>'.
					"Pedido: " . $order_id . '<br>';
					
					//seleciona o e-mail cadastrado na página de configurações do plugin
					$to = get_option( 'wc_leadlovers_debug_email' );
					//definição padrão de cabeçalho do e-mail
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					//envia o e-mail
					wp_mail( $to, $subject, $body, $headers );
				}
			}
		}
	}
}



//--------------------------------------------
//---- D E B U G ---- Deixo aqui para facilitar o teste de valores através de notices
//--------------------------------------------
/*
function debug_admin_notice() {
	$class = 'notice notice-info';
	//$message = get_post_meta( $post->ID, 'woocommerce_sku', true );//"Check?  " . get_option( 'woocommerce_leadlovers_integration_product' );
	//$mesage = get_template_directory_uri();
	$message = WP_PLUGIN_DIR . '/llregonproduct/js/llregonproduct.js';
	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}
add_action( 'admin_notices', 'debug_admin_notice' );
*/
