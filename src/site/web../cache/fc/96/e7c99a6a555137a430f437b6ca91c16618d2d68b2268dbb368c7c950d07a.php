<?php

/* login.twig.html */
class __TwigTemplate_fc96e7c99a6a555137a430f437b6ca91c16618d2d68b2268dbb368c7c950d07a extends Twig_Template
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
            'title' => array($this, 'block_title'),
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
    public function block_title($context, array $blocks = array())
    {
        echo twig_escape_filter($this->env, (isset($context["title"]) ? $context["title"] : null), "html", null, true);
    }

    // line 4
    public function block_content($context, array $blocks = array())
    {
        // line 5
        echo "<form class=\"form-signin\" action=\"";
        echo twig_escape_filter($this->env, (isset($context["baseUrl"]) ? $context["baseUrl"] : null), "html", null, true);
        echo "/login\" method=\"post\">
  <h2 class=\"form-signin-heading\">Please sign in</h2>
  <label for=\"inputEmail\" class=\"sr-only\">Email address</label>
  <input type=\"email\" name=\"email\" id=\"inputEmail\" class=\"form-control\" placeholder=\"Email address\" required autofocus>
  <label for=\"inputPassword\" class=\"sr-only\">Password</label>
  <input type=\"password\" name=\"password\" id=\"inputPassword\" class=\"form-control\" placeholder=\"Password\" required>
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
        return array (  46 => 5,  43 => 4,  37 => 3,  11 => 1,);
    }
}
