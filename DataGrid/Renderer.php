<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2004 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Andrew Nagy <asnagy@webitecture.org>                         |
// +----------------------------------------------------------------------+
//
// $Id$

require_once 'Structures/DataGrid/Core.php';

/**
 * Structures_DataGrid_Renderer Class
 *
 * This class handles managing the output for the DataGrid.
 * By default, the output is handled by the HTML_Table renderer.
 *
 * @version  $Revision$
 * @author   Andrew S. Nagy <asnagy@webitecture.org>
 * @access   public
 * @package  Structures_DataGrid
 * @category Structures
 */
class Structures_DataGrid_Renderer extends Structures_DataGrid_Core
{
    var $renderer;

    /**
     * Constructor
     *
     * Determins the appropriate renderer to use.  Uses the HTML_Table renderer
     * as the default.
     *
     * @param  string   $renderer   The renderer to use.
     * @access public
     */
    function Structures_DataGrid_Renderer($renderer = DATAGRID_RENDER_TABLE)
    {
        if (PEAR::isError($this->setRenderer($renderer))) {
            $this->setRenderer(DATAGRID_RENDER_TABLE);
        }
        
        // Automatic handling of GET/POST/COOKIE variables
        $this->_parseHttpRequest();
    }

    /**
     * Render
     *
     * Renders that output by calling the specified renderer's render method.
     *
     * @param  string   $limit  The row limit per page.
     * @param  string   $page   The current page viewed.
     * @access public
     */
    function render()
    {
        return $this->renderer->render($this);
    }

    function fetchDataSource()
    {
        if ($this->_dataSource != null) {
            $recordSet = $this->_dataSource->fetch(
                            (($this->page-1)*$this->rowLimit),
                            $this->rowLimit, $this->sortArray[0],
                            $this->sortArray[1]);
            if (PEAR::isError($recordSet)) {
                return $recordSet;
            } else {
                $this->recordSet = $recordSet;
                if (count($columnSet = $this->_dataSource->getColumns())) {
                    $this->columnSet = $columnSet;
                }
            }
        }
    }
    
    /**
     * Get Renderer
     *
     * Retrieves the renderer object as a reference
     *
     * @access public
     */
    function &getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Set Renderer
     *
     * Defines which renderer to be used by the DataGrid
     *
     * @param  string   $renderer       The defined renderer string
     * @access public
     */
    function setRenderer($renderer)
    {
        $class = 'Structures_DataGrid_Renderer_' . $renderer;
        $file = 'Structures/DataGrid/Renderer/' . $renderer . '.php';

        if (@include_once($file)) {
            $this->renderer = new $class();
        } else {
            return new PEAR_Error('Invalid renderer');
        }

        return true;
    }

    /**
     * Set Default Headers
     *
     * This method handles determining if column headers need to be set.
     *
     * @access private
     */
    function _setDefaultHeaders()
    {
        if ((!count($this->columnSet)) && (count($this->recordSet))) {
            $arrayKeys = array_keys($this->recordSet[0]);
            foreach ($arrayKeys as $key) {
                $width = ceil(100/count($arrayKeys));
                $column = new Structures_DataGrid_Column($key, $key, $key,
                                                         array('width' =>
                                                               $width.'%'));
                $this->addColumn($column);
            }
        }
    }
    
    /**
     * Parse HTTP Request parameters
     *
     * @access  private
     * @return  array      Associative array of parsed arguments, each of these 
     *                     defaulting to null if not found. 
     */
    function _parseHttpRequest()
    {
        // Determine parameter prefix
        if ((isset($this->renderer->requestPrefix)) &&
            ($this->renderer->requestPrefix != '')) {
            $prefix = $this->renderer->requestPrefix;
        } else {
            $prefix = null;
        }

        // Add values to arguments
        if (isset($_REQUEST[$prefix . 'page'])) {
            $this->page = $_REQUEST[$prefix . 'page'];
        }
        
        if (isset($_REQUEST[$prefix . 'orderBy'])) {
            $this->sortArray[0] = $_REQUEST[$prefix . 'orderBy'];
        }
        
        if (isset($_REQUEST[$prefix . 'direction'])) {
            $this->sortArray[1] = $_REQUEST[$prefix . 'direction'];
        }
    }

}

?>