<?php
/**
 * Unit Tests for Structures_DataGrid
 * 
 * PHP versions 4 and 5
 *
 * LICENSE:
 * 
 * Copyright (c) 1997-2007, Olivier Guilyardi <olivier@samalyse.com>,
 *                          Mark Wiesemann <wiesemann@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the 
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products 
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * CVS file id: $Id$
 * 
 * @version  $Revision$
 * @package  Structures_DataGrid
 * @author   Olivier Guilyardi <olivier@samalyse.com>
 * @author   Mark Wiesemann <wiesemann@php.net>
 * @category Structures
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */


require_once 'Structures/DataGrid/DataSource.php';
require_once 'PEAR.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * DataSource core tests
 */
class DataSourceTest extends PHPUnit_Framework_TestCase
{
    protected $datasource;
    protected $data = array(
                array('num' => '1', 'the str' => 'test'),
                array('num' => '1', 'the str' => 'présent'),
                array('num' => '2', 'the str' => 'viel spaß'),
                array('num' => '3', 'the str' => ''),
            );

    public function __construct()
    {
        parent::__construct();
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($this, 'onPearError'));
    }

    public function onPearError($error)
    {
        $this->fail(
            "------------------------\n".
            "PEAR Error: " . $error->toString() . "\n" .
            "------------------------\n");
    }

    public function setUp()
    {
        $class = $this->getDriverClassName();
        $file = str_replace('_', '/', $class) . '.php';
        if (!$fp = @fopen($file, 'r', true)) {
            $this->markTestSkipped("Driver unavailable: $class");
        }
        fclose($fp);
        require_once($file);
        $this->datasource = new $class();
    }

    public function tearDown()
    {
        unset($this->datasource);
    }

    public function testFetchAll()
    {
        $this->bindDefault();
        $this->assertEquals($this->data, $this->datasource->fetch());
    }

    public function testLimit()
    {
        $this->bindDefault();
        $records = $this->datasource->fetch(1);
        $expected = array_slice($this->data, 1);
        $this->assertEquals($expected, $records);
        $this->bindDefault();
        $records = $this->datasource->fetch(1,1);
        $this->assertEquals($this->data[1], $records[0]);
    }

    public function testCountBeforeFetch()
    {
        $this->bindDefault();
        $this->assertEquals(count($this->data), $this->datasource->count());
        $this->assertEquals($this->data, $this->datasource->fetch());
    }

    public function testCountAfterFetch()
    {
        $this->bindDefault();
        $this->assertEquals($this->data, $this->datasource->fetch());
        $this->assertEquals(count($this->data), $this->datasource->count());
    }

    public function testSort()
    {
        $this->bindDefault();
        $expected = array(
            array('num' => '3', 'the str' => ''),
            array('num' => '2', 'the str' => 'viel spaß'),
            array('num' => '1', 'the str' => 'présent'),
            array('num' => '1', 'the str' => 'test'),
        );
        $this->datasource->sort('num', 'DESC');
        $this->assertEquals($expected, $this->datasource->fetch());
    }

    public function testMultiSort()
    {
        $this->bindDefault();
        if ($this->datasource->hasFeature('multiSort')) {
            $expected = array(
                array('num' => '3', 'the str' => ''),
                array('num' => '2', 'the str' => 'viel spaß'),
                array('num' => '1', 'the str' => 'test'),
                array('num' => '1', 'the str' => 'présent'),
            );
            $this->datasource->sort(array('num' => 'DESC', 'the str' => 'DESC'));
            $this->assertEquals($expected, $this->datasource->fetch());
        } else {
            $this->markTestSkipped( 'Driver does not support multiSort');
        }
    }
}

?>