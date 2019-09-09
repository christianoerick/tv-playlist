<?php
class Tv
{
  protected $vlc = true;
  
  protected $menu = [];
  
  protected $prefixo = '';
  
  protected $titulo = '';
  
  protected $modulo = '';
  
  public function getLink($complemento, $item)
  {
    if ($this->vlc)
    {
      $retorno = $this->get('link', null, $item);
      if ($retorno)
      {
        $retorno = 'http://:12345@10.0.0.10:8080/requests/status.xml?command=in_play&input='.$retorno.'&name='.$item['nome'].'" target="iframe" class="js_vlc';
      }
      else
      {
        $retorno = $this->prefixo.$complemento;
      }
    }
    else
    {
      $retorno = $this->get('link', $this->prefixo.$complemento, $item);
    }
    
    return $retorno;
  }
  
  public function getMenu()
  {
    return $this->menu;
  }
  
  public function getTitulo()
  {
    return $this->titulo;
  }
  
  public function getModulo()
  {
    return $this->modulo;
  }
  
  public function lista($arquivo_json)
  {
    $retorno = [
      'status' => false,
    ];
    
    if (!file_exists($arquivo_json))
    {
      $retorno['message'] = 'Arquivo (json) não existe';
    }
    else
    {
      $lista = json_decode(file_get_contents($arquivo_json), true);
      
      $this->menu = $lista;
      
      $this->prefixo = './?modulo=';
      
      $this->titulo = 'Home';
    }
    
    $this->modulo = $this->get('modulo');
    
    if ($this->modulo && $this->exists($this->modulo, $lista))
    {
      $lista_pagina = $lista[$this->modulo];
      $this->titulo = $this->trataNome($lista_pagina['nome']);
      $lista = $lista_pagina['lista'];
      
      $this->prefixo .= $this->modulo.'&opcao=';
      
      if (in_array($this->modulo, [ 'radios', 'documentarios', 'shows' ]))
      {
        $lista = [];
        foreach ($lista_pagina['lista']['listagem']['lista'] as $k => $estacao)
        {
          $lista[$k] = [
            'nome' => $estacao['tvg-name'],
            'link' => $estacao['link'],
            'imagem' => $estacao['tvg-logo'],
          ];
        }
      }
      
      $opcao = $this->get('opcao');
      if ($opcao && $this->exists($opcao, $lista))
      {
        $lista_pagina = $lista[$opcao];
        $this->titulo .= ' &raquo; '.$this->trataNome($lista_pagina['nome']);
        
        $lista = [];
        if ($this->exists('temporadas', $lista_pagina))
        {
          $this->prefixo .= $opcao.'&temporada=';
          foreach ($lista_pagina['temporadas'] as $k => $temporada)
          {
            $lista[$k] = [
              'nome' => 'Temporada '.filter_var($k, FILTER_SANITIZE_NUMBER_INT),
            ];
          }
        }
        else
        {
          $this->prefixo .= $opcao.'&canal=';
          foreach ($lista_pagina['lista'] as $k => $item)
          {
            $lista[$k] = [
              'nome' => ($this->modulo != 'novelas')?$item['tvg-name']:'Capitulo '.filter_var($item['tvg-name'], FILTER_SANITIZE_NUMBER_INT),
              'link' => $item['link'],
              'imagem' => ($this->modulo != 'novelas')?$item['tvg-logo']:'',
            ];
          }
        }
        
        $temporada = $this->get('temporada');
        if (!is_null($temporada) && $this->exists($temporada, $lista))
        {
          $item = $lista_pagina['temporadas'][$temporada];
          $this->titulo .= ' &raquo; Temporada '.filter_var($temporada, FILTER_SANITIZE_NUMBER_INT);
          $this->prefixo .= $temporada.'&episodio=';
          
          $lista = [];
          foreach ($item['episodios'] as $k => $atual)
          {
            $lista[$k] = [
              'nome' => 'Episodio '.$atual['tvg-name'],
              'link' => $atual['link'],
              'imagem' => $item['tvg-logo'],
            ];
          }
          
          $retorno = [
            'status' => true,
            'data' => $lista,
          ];
        }
        else
        {
          $retorno = [
            'status' => true,
            'data' => $lista,
          ];
        }
      }
      else
      {
        $listagem = [];
        foreach ($lista as $chave => $item)
        {
          $listagem[$chave] = [
            'chave' => $chave,
            'nome' => $this->trataNome($item['nome']),
            'imagem' => ($this->modulo == 'canais')?'':$item['imagem'],
          ];
          
          if (!in_array($this->modulo, [ 'canais', 'filmes', 'series', 'novelas' ]))
          {
            $listagem[$chave]['link'] = $this->get('link', null, $item);
          }
        }
        
        $retorno = [
          'status' => true,
          'data' => $listagem,
        ];
      }
    }
    else
    { 
      $listagem = [];
      foreach ($lista as $chave => $item)
      {
        $listagem[$chave] = [
          'chave' => $chave,
          'nome' => $this->trataNome($item['nome']),
        ];
      }
      
      $retorno = [
        'status' => true,
        'data' => $listagem,
      ];
    }
    
    return $retorno;
  }
  
  protected function trataNome($nome)
  {
    $lista = [
      'Radios' => 'Rádios',
      'Documentarios' => 'Documentários',
      'Series' => 'Séries',
      'Acao' => 'Ação',
      'Comedia' => 'Comédia',
      'Animacao' => 'Animação',
      'Ficcao' => 'Ficção',
      'Fantasia e Ficcao' => 'Fantasia e Ficção',
      'Lancamentos' => 'Lançamentos',
    ];
    
    return $this->get($nome, $nome, $lista);
  }
  
  public function converteArquivo($arquivo_lista, $arquivo_json)
  {
    $retorno = [
      'status' => false,
    ];
    
    if (!file_exists($arquivo_lista))
    {
      $retorno['message'] = 'Arquivo (lista) não existe';
    }
    else if (!trim($arquivo_json))
    {
      $retorno['message'] = 'Arquivo (json) não definido';
    }
    else
    {
      $conteudo = file($arquivo_lista, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      
      $grupo = $nome = $temporada = null;
      $lista = [];
      
      foreach ($conteudo as $linha)
      {
        if (strpos(trim($linha), '#EXTM3U') === 0)
        {
          continue;
        }
        
        if (strpos(trim($linha), '#EXTINF') === 0)
        {
          $dados = $this->getAttributes($linha);
          
          list($grupo, $nome) = explode('|', $dados['group-title'], 2);
          $grupo = trim($grupo);
          $nome = trim($nome);
          
          $grupo_chave = strtolower($grupo);
          $grupo_chave = ($grupo_chave == 'serie')?'series':$grupo_chave;
          $nome = (in_array($grupo_chave, [ 'radios', 'shows', 'documentarios' ]))?'listagem':$nome;
          $nome_chave = $this->limpa($nome);
    
          if (!$this->exists($grupo_chave, $lista))
          {
            $lista[$grupo_chave] = [
              'nome' => $grupo,
              'lista' => [],
            ];
          }
          
          if (!$this->exists($nome_chave, $lista[$grupo_chave]['lista']))
          {
            $temporada = null;
            $lista[$grupo_chave]['lista'][$nome_chave] = [
              'nome' => $nome,
              'imagem' => '',
              'contador' => 0,
              'lista' => [],
            ];
          }
              
          unset($dados['group-title']);
          
          if (in_array($grupo_chave, [ 'series', 'novelas' ]))
          {
            $dados['tvg-name'] = trim(substr($dados['tvg-name'], -8));
            
            if ($grupo_chave == 'series')
            {
              list($temporada, $episodio) = explode('E', $dados['tvg-name'], 2);
              $dados['tvg-name'] = $episodio;
              $temporada = trim($temporada);
              
              if ($this->exists('lista', $lista[$grupo_chave]['lista'][$nome_chave]))
              {
                unset($lista[$grupo_chave]['lista'][$nome_chave]['lista']);
                $lista[$grupo_chave]['lista'][$nome_chave]['temporadas'] = [];
              }
              
              if (!$this->exists($temporada, $lista[$grupo_chave]['lista'][$nome_chave]['temporadas']))
              {
                $lista[$grupo_chave]['lista'][$nome_chave]['temporadas'][$temporada] = [
                  'nome' => $temporada,
                  'contador' => 0,
                  'episodios' => [],
                ]; 
              }
            }
            else if ($grupo_chave == 'novelas')
            {
              $dados['tvg-name'] = trim(ltrim($dados['tvg-name'], 'S01'));
            }
          }
          
          if ($temporada)
          {
            $contador = $lista[$grupo_chave]['lista'][$nome_chave]['temporadas'][$temporada]['contador'];
            $lista[$grupo_chave]['lista'][$nome_chave]['temporadas'][$temporada]['episodios'][$contador] = $dados;
            $lista[$grupo_chave]['lista'][$nome_chave]['temporadas'][$temporada]['contador']++;
            if ($this->exists('tvg-logo', $dados) && $dados['tvg-logo'] && !$lista[$grupo_chave]['lista'][$nome_chave]['imagem'])
            {
              $lista[$grupo_chave]['lista'][$nome_chave]['imagem'] = $dados['tvg-logo'];
            }
          }
          else
          {
            $contador = $lista[$grupo_chave]['lista'][$nome_chave]['contador'];
            $lista[$grupo_chave]['lista'][$nome_chave]['lista'][$contador] = $dados;
            $lista[$grupo_chave]['lista'][$nome_chave]['contador']++;
            if ($this->exists('tvg-logo', $dados) && $dados['tvg-logo'] && !$lista[$grupo_chave]['lista'][$nome_chave]['imagem'] && $grupo_chave != 'filmes')
            {
              $lista[$grupo_chave]['lista'][$nome_chave]['imagem'] = $dados['tvg-logo'];
            }
          }
        }
        else
        {
          if ($temporada)
          {
            $lista[$grupo_chave]['lista'][$nome_chave]['temporadas'][$temporada]['episodios'][$contador]['link'] = $linha;
          }
          else
          {
            $lista[$grupo_chave]['lista'][$nome_chave]['lista'][$contador]['link'] = $linha;
          }
        }
      }
      
      if (@file_put_contents($arquivo_json, json_encode($lista)))
      {
        $retorno = [
          'status' => true,
        ];
      }
      else
      {
        $retorno['message'] = 'Não foi possível salvar o arquivo json, verifique as permissões';
      }
    }
    
    return $retorno;
  }
  
  protected function getAttributes($linha)
  {
    $retorno = [];
    
    $parametros = explode('" ', $linha);
    foreach ($parametros as $parametro)
    {
      list($chave, $item) = explode('="', $parametro);
      $chave = explode(' ', $chave);
      $chave = end($chave);
      list($item) = explode('"', $item);
      $retorno[$chave] = $item;
    }
    
    return $retorno;
  }
  
  protected function get($indice, $padrao = null, $array = null)
  {
    $array = !is_array($array) ? $_GET : $array;
    
    return array_key_exists($indice, $array) ? $array[$indice] : $padrao;
  }
  
  protected function exists($indice, $array)
  {
    return array_key_exists($indice, $array);
  }
  
  protected function limpa($valor)
  {
    $valor = preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"), $valor);
    $valor = str_replace(array('ç', 'Ç'), array('c', 'C'), $valor);
    $valor = strtolower($valor);
    $valor = str_replace("'", "-", $valor);
    $valor = str_replace("'", '', $valor);
    $valor = str_replace(array('[\', \']', '&amp;'), '', $valor);
    $valor = preg_replace('/\[.*\]/U', '', $valor);
    $valor = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $valor);
    $valor = htmlentities($valor, ENT_COMPAT, 'utf-8');
    $valor = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo|amp);/i', '\\1', $valor );
    $valor = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $valor);
    $valor = str_replace('-amp-', '-', $valor);
    return trim($valor);
  }
}
?>