<?php

/* full_width.twig.html */
class __TwigTemplate_bb7de6b775e7d8d0250859550bc711d8a743db45876edf85ef30cf6004d3120c extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'content' => array($this, 'block_content'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 2
        echo "<!DOCTYPE html>
<html lang=\"en\">
  <head>
    <meta charset=\"utf-8\">
    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
    <meta name=\"description\" content=\"\">
    <meta name=\"author\" content=\"\">
    <title>";
        // line 10
        $this->displayBlock('title', $context, $blocks);
        echo twig_escape_filter($this->env, (isset($context["site_title"]) ? $context["site_title"] : null), "html", null, true);
        echo "</title>
    <!-- Bootstrap core CSS -->
    <link rel=\"stylesheet\" href=\"";
        // line 12
        echo twig_escape_filter($this->env, $this->env->getExtension('slim')->site("/assets/css/bootstrap.min.css"), "html", null, true);
        echo "\" />  
    <link rel=\"stylesheet\" href=\"";
        // line 13
        echo twig_escape_filter($this->env, $this->env->getExtension('slim')->site("/assets/css/navbar.css"), "html", null, true);
        echo "\" />    
    <!--[if lt IE 9]>
      <script src=\"https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js\"></script>
      <script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>
    <![endif]-->
  </head>
  <body>
    <div class=\"container\">
      ";
        // line 21
        $this->displayBlock('content', $context, $blocks);
        // line 22
        echo "    </div> <!-- /container -->
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->  
    <script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js\"></script>
    <script src=\"";
        // line 27
        echo twig_escape_filter($this->env, $this->env->getExtension('slim')->site("/assets/js/bootstrap.min.js"), "html", null, true);
        echo "\"></script>     
  </body>
</html>";
    }

    // line 10
    public function block_title($context, array $blocks = array())
    {
    }

    // line 21
    public function block_content($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "full_width.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 21,  68 => 10,  61 => 27,  54 => 22,  52 => 21,  41 => 13,  37 => 12,  31 => 10,  21 => 2,);
    }
}
