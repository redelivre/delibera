<?php
class NotificarTest extends WP_UnitTestCase
{
    function setUP()
    {
        parent::setUp();
        
        global $deliberaThemes;
        $deliberaThemes = new DeliberaThemes;
        
        $user = get_user_by('id', 1);
        $user->add_cap('votar');
        $this->post_id = $this->factory->post->create(array('post_type' => 'pauta'));
    }


	function testNotificarFimDePrazo()
	{
	    global $phpmailer;

        if (!is_multisite()) {
            $expected = 'Olá admin,

O prazo para a relatoria da pauta "Post title 1" terminou, agora você poderá votar nas propostas que foram encaminhadas durante o processo de discussão e sistematizadas pela relatoria. 
Você pode acompanhar a origem de todas as propostas, de modo que garanta o controle social da sistematização das propostas.

<br/><br/>Origem: <a href="http://example.org/?post_type=pauta">Test Blog</a><br/>Pauta: <a href="http://example.org/?pauta=post-title-1">Post title 1</a><br/><br/>Para ver a mensagem na página, clique aqui: http://example.org/?pauta=post-title-1
';
        } else {
            $expected = 'Olá admin,

O prazo para a relatoria da pauta "Post title 1" terminou, agora você poderá votar nas propostas que foram encaminhadas durante o processo de discussão e sistematizadas pela relatoria. 
Você pode acompanhar a origem de todas as propostas, de modo que garanta o controle social da sistematização das propostas.

<br/><br/>Origem: <a href="http://example.org/blog/pauta/">Test Blog</a><br/>Pauta: <a href="http://example.org/blog/pauta/post-title-1/">Post title 1</a><br/><br/>Para ver a mensagem na página, clique aqui: http://example.org/blog/pauta/post-title-1/
';
        }
        
        wp_set_object_terms($this->post_id, 'relatoria', 'situacao', false);
        
        delibera_notificar_fim_prazo(array('post_ID' => $this->post_id));
        
		$this->assertEquals($expected, $phpmailer->mock_sent[0]['body']);
	}
}

