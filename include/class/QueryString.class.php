<?php

  class QueryStringElement
  {
    var $param;
    var $value;

    function QueryStringElement($param, $value = '')
    {
      $this->param = $param;
      $this->value = $value;
    }

    function getQueryElement($separator = '=')
    {
      // returns '$param=$value' if the param has an associated value, otherwise returns '$param'
      if ($this->value != '')
      {
        $s = $this->param . $separator . $this->value;
      }
      else
      {
        $s = $this->param;
      }
      return $s;
    }
  }

  class QueryString
  {
    var $elements; // Array of QueryStringElement

    function QueryString()
    {
    }

    function reduce($paramToLoose, $prefix = '', $suffix = '')
    {
      $this->looseElement($paramToLoose);
    }

    function reduceCurrent($paramToLoose, $prefix = '', $suffix = '')
    {
      $queryString = new QueryString();
      $queryString->loadCurrentQuery();
      $queryString->looseElement($paramToLoose);
      return $queryString->getQueryString($prefix, $suffix);
    }

    function extract($paramsToKeep, $prefix = '', $suffix = '')
    {
      $extractedQueryString = new QueryString();
      if (!empty($this->elements))
      {
        foreach ($this->elements as $element)
        {
          if (in_array($element->param, $paramsToKeep))
          {
            $extractedQueryString->addElement($element->param, $element->value);
          }
        }
      }
      return $extractedQueryString->getQueryString($prefix, $suffix);
    }

    function extractFromCurrent($paramsToKeep, $prefix = '', $suffix = '')
    {
      $queryString = new QueryString();
      $queryString->loadCurrentQuery();
      $queryString->reduce($paramsToKeep, $prefix, $suffix);
      return $queryString;
    }

    function processCurrent($paramsToKeep, $paramsToLoose, $prefix = '', $suffix = '')
    {
      $queryString = new QueryString();
      $queryString->loadCurrentQuery();
      $queryString->extract($paramsToKeep);
      $queryString->reduce($paramsToLoose, $prefix, $suffix);
      return $queryString->getQueryString($prefix, $suffix);
    }

    function loadCurrentQuery()
    {
      $this->loadQuery($_SERVER['QUERY_STRING']);
    }

    function loadQuery($rawQueryString)
    {
      if (isset($rawQueryString{0}) && $rawQueryString{0} == '?') { $rawQueryString = substr($rawQueryString, 1); } // Loose the first char if it is a '?'

      $queryElements = explode ('&', $rawQueryString);
      foreach ($queryElements as $queryElement)
      {
        if ($queryElement != null)
        {
          $queryElement = explode ('=', $queryElement);
          if (isset($queryElement[1]))
          {
            $this->addElement($queryElement[0], $queryElement[1]);
          }
          else
          {
            $this->addElement($queryElement[0]);
          }
        }
      }
    }

    function getQueryString($prefix = '', $suffix = '')
    {
      $s = null;
      if (!$this->isEmpty())
      {
        foreach ($this->elements as $element)
        {
          $s .= $element->getQueryElement().'&';
        }
        $s = substr($s, 0, -1 ); // Loose the last char of the query string as it is a '&'
        $s = $prefix.$s.$suffix;
      }
      return htmlspecialchars($s);
    }

    function addElement($param, $value = '')
    {
      $this->elements[] = new QueryStringElement($param, $value);
    }

    function looseElement($paramToLoose)
    {
      $newElements = null;
      if (!$this->isEmpty())
      {
        foreach ($this->elements as $element)
        {
          if ($element->param != $paramToLoose)
          {
           $newElements[] = new QueryStringElement($element->param, $element->value);
          }
        }
      }
      $this->elements = isset($newElements) ? $newElements : null;
    }

    function isEmpty()
    {
      return empty($this->elements) or count($this->elements) < 1;
    }

    function getCurrentUrl()
    {
      // Get the current script filename, by isolating text after the last slash
      $scriptFilename = explode('/', $_SERVER['SCRIPT_NAME']);
      $scriptFilename = $scriptFilename[count($scriptFilename)-1];

      // Get the current path, by getting rid of the $scriptFilename from $_SERVER['SCRIPT_NAME'] value
      preg_match ('/(.*)'.$scriptFilename.'/', $_SERVER['SCRIPT_NAME'], $path);
      $path = $path[1]; // Path[1] contains the current path

      return 'http://'.$_SERVER['SERVER_NAME'].$path;
    }
  }

?>
