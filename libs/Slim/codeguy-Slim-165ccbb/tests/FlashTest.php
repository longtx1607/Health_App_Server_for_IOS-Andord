<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart <info@slimframework.com>
 * @copyright   2011 Josh Lockhart
 * @link        http://www.slimframework.com
 * @license     http://www.slimframework.com/license
 * @version     2.3.5
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class FlashTest extends PHPUnit_Framework_TestCase
{
    public function tearDown() {
        \Mockery::close();
    }

    /**
     * test flash for next request
     */
    public function testFlashForNextRequest()
    {
        $dataSource = new \ArrayObject();
        $session = \Mockery::mock('\Slim\Session[isStarted,initialize]');
        $session->setDataSource($dataSource);
        $session->shouldReceive('isStarted')->once()->withNoArgs()->andReturn(false);
        $session->shouldReceive('initialize')->once()->withNoArgs()->andReturnNull();
        $session->start();

        $flash = new \Slim\Flash($session);
        $flash->next('foo', 'bar');
        $flash->save();
        $this->assertEquals('bar', $session['slimflash']['foo']);
        $this->assertEmpty($flash->getMessages());
    }

    /**
     * test flash with existing session data
     */
    public function testFlashWithExistingSessionData()
    {
        $dataSource = new \ArrayObject(array(
            'slim.session' => array(
                'slimflash' => array(
                    'foo' => 'bar'
                )
            )
        ));
        $session = \Mockery::mock('\Slim\Session[isStarted,initialize]');
        $session->setDataSource($dataSource);
        $session->shouldReceive('isStarted')->once()->withNoArgs()->andReturn(false);
        $session->shouldReceive('initialize')->once()->withNoArgs()->andReturnNull();
        $session->start();

        $flash = new \Slim\Flash($session);
        $messages = $flash->getMessages();
        $this->assertEquals('bar', $messages['foo']);
    }

    /**
     * test flash keep with existing session data
     */
    public function testFlashKeepWithExistingSessionData()
    {
        $dataSource = new \ArrayObject(array(
            'slim.session' => array(
                'slimflash' => array(
                    'foo' => 'bar'
                )
            )
        ));
        $session = \Mockery::mock('\Slim\Session[isStarted,initialize]');
        $session->setDataSource($dataSource);
        $session->shouldReceive('isStarted')->once()->withNoArgs()->andReturn(false);
        $session->shouldReceive('initialize')->once()->withNoArgs()->andReturnNull();
        $session->start();

        $flash = new \Slim\Flash($session);
        $flash->keep();
        $messages = $flash->getMessages();
        $this->assertEquals('bar', $messages['foo']);
        $this->assertEquals('bar', $session['slimflash']['foo']);
    }

    /**
     * test flash now
     */
    public function testFlashNow()
    {
        $dataSource = new \ArrayObject();
        $session = \Mockery::mock('\Slim\Session[isStarted,initialize]');
        $session->setDataSource($dataSource);
        $session->shouldReceive('isStarted')->once()->withNoArgs()->andReturn(false);
        $session->shouldReceive('initialize')->once()->withNoArgs()->andReturnNull();
        $session->start();

        $flash = new \Slim\Flash($session);
        $flash->now('foo', 'bar');
        $messages = $flash->getMessages();
        $this->assertEquals('bar', $messages['foo']);
    }

    /**
     * test flash now with existing session data
     */
    public function testFlashNowWithExistingSessionData()
    {
        $dataSource = new \ArrayObject(array(
            'slim.session' => array(
                'slimflash' => array(
                    'foo' => 'bar'
                )
            )
        ));
        $session = \Mockery::mock('\Slim\Session[isStarted,initialize]');
        $session->setDataSource($dataSource);
        $session->shouldReceive('isStarted')->once()->withNoArgs()->andReturn(false);
        $session->shouldReceive('initialize')->once()->withNoArgs()->andReturnNull();
        $session->start();

        $flash = new \Slim\Flash($session);
        $flash->now('abc', '123');
        $messages = $flash->getMessages();
        $this->assertEquals('bar', $messages['foo']);
        $this->assertEquals('123', $messages['abc']);
    }
}
