<?php

/* login.twig.html */
class __TwigTemplate_502779242b89be17a0178eafbb01e35c7f016169bfd501f460020d3871bd7953 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        // line 1
        try {
            $this->parent = $this->env->loadTemplate("full_width.twig.html");
        } catch (Twig_Error_Loader $e) {
            $e->setTemplateFile($this->getTemplateName());
            $e->setTemplateLine(1);

            throw $e;
        }

        $this->blocks = array(
            'content' => array($this, 'block_content'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "full_width.twig.html";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_content($context, array $blocks = array())
    {
        // line 4
        echo "<form class=\"form-signin\">
  <h2 class=\"form-signin-heading\">Please sign in</h2>
  <label for=\"inputEmail\" class=\"sr-only\">Email address</label>
  <input type=\"email\" id=\"inputEmail\" class=\"form-control\" placeholder=\"Email address\" required autofocus>
  <label for=\"inputPassword\" class=\"sr-only\">Password</label>
  <input type=\"password\" id=\"inputPassword\" class=\"form-control\" placeholder=\"Password\" required>
  <div class=\"checkbox\">
    <label>
      <input type=\"checkbox\" value=\"remember-me\"> Remember me
    </label>
  </div>
  <button class=\"btn btn-lg btn-primary btn-block\" type=\"submit\">Sign in</button>
</form>
";
    }

    public function getTemplateName()
    {
        return "login.twig.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  39 => 4,  36 => 3,  11 => 1,);
    }
}
