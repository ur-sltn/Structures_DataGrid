<?php
/**
 * Stream wrapper for CSV DataSource driver
 * 
 * PHP versions 4 and 5
 *
 * LICENSE:
 * 
 * Copyright (c) 1997-2007, Andrew Nagy <asnagy@webitecture.org>,
 *                          Olivier Guilyardi <olivier@samalyse.com>,
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
 * CSV file id: $Id$
 * 
 * @version  $Revision$
 * @package  Structures_DataGrid_DataSource_CSV
 * @category Structures
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 */

/**
 * Stream wrapper for CSV DataSource driver
 *
 * This class is a stream wrapper for CSV data.
 *
 * @version  $Revision$
 * @author   Andrew Nagy <asnagy@webitecture.org>
 * @author   Olivier Guilyardi <olivier@samalyse.com>
 * @author   Mark Wiesemann <wiesemann@php.net>
 * @access   public
 * @package  Structures_DataGrid_DataSource_CSV
 * @category Structures
 */
class Structures_DataGrid_DataSource_CSV_Stream
{
    /**
     * The current position in the stream
     *
     * @var integer
     * @access private
     */
    var $position;

    /**
     * A string holding the stream data
     *
     * @var string
     * @access private
     */
    var $varname;

    /**
     * This method is called immediately after the stream object is created.
     *
     * @param string    $path           Path (not used)
     * @param string    $mode           Mode (fopen(), not used)
     * @param integer   $options        Options (not used)
     * @param string    $opened_path    The opened path (not used)
     * @return boolean                  true on success, false on error
     */
    function stream_open($path, $mode, $options, &$opened_path)
    {
        $this->varname = '';
        $this->position = 0;
        return true;
    }

    /**
     * This method is called in response to fread() and fgets() calls on the
     * stream. 
     *
     * @param integer   $count          The number of bytes that should be read
     * @return string                   The data that was read
     */
    function stream_read($count)
    {
        $ret = substr($this->varname, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    /**
     * This method is called in response to fwrite() calls on the stream. 
     *
     * @param integer   $data           The data string that should be stored
     * @return string                   The number of bytes that was written
     */
    function stream_write($data)
    {
        $left = substr($this->varname, 0, $this->position);
        $right = substr($this->varname, $this->position + strlen($data));
        $this->varname = $left . $data . $right;
        $this->position += strlen($data);
        return strlen($data);
    }

    /**
     * This method is called in response to feof() calls on the stream.
     *
     * @return boolean                  Whether the read/write position is at
     *                                  the end of the stream 
     */
    function stream_eof()
    {
        return $this->position >= strlen($this->varname);
    }

    /**
     * This method is called in response to ftell() calls on the stream.
     *
     * @return integer                  The current read/write position of the
     *                                  stream
     */
    function stream_tell()
    {
        return $this->position;
    }

    /**
     * This method is called in response to fseek() calls on the stream.
     *
     * @param integer $offset           Offset of the new position, added to the
     *                                  $whence position
     * @param integer $whence           Start position; one of: SEEK_SET,
     *                                  SEEK_CUR, SEEK_END
     * @return boolean                  true if position was changed, false if
     *                                  position could not be changed
     */
    function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->varname) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case SEEK_END:
                if (strlen($this->varname) + $offset >= 0) {
                     $this->position = strlen($this->varname) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            default:
                return false;
        }
    }
}

/* vim: set expandtab tabstop=4 shiftwidth=4: */
?>
