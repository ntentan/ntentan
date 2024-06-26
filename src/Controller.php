<?php
namespace ntentan;

use Psr\Http\Message\ResponseInterface;

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application logic. They are stored in modules and they contain methods
 * which are called from the url. Parameters to the methods are also passed
 * through the URL. If a method is not specified, the default method is called.
 * The methods called by the controllers are expected to set data into variables
 * which are later rendered as output to the end user through views.
 *
 * @author  James Ekow Abaka Ainooson
 * @todo    There must be a controller interface to be satisfied by all controllers
 */
interface Controller
{
       
}
