<?php
/**
 * PermissionProvider Header Test Class
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2010.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Bernd Oberknapp <bo@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:unit_tests Wiki
 */
namespace VuFindTest\Role\PermissionProvider;
use VuFind\Role\PermissionProvider\Header;

/**
 * PermissionProvider Header Test Class
 *
 * @category VuFind2
 * @package  Tests
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @author   Bernd Oberknapp <bo@ub.uni-freiburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     http://vufind.org/wiki/vufind2:unit_tests Wiki
 */
class HeaderTest extends \VuFindTest\Unit\TestCase
{
    /**
     * Test single option with matching string
     *
     * @return void
     */
    public function testStringTrue()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            'testheader testvalue',
            ['loggedin']
        );
    }

    /**
     * Test option array with matching string
     *
     * @return void
     */
    public function testArrayTrue()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader testvalue'],
            ['loggedin']
        );
    }

    /**
     * Test multiple options with matching headers
     *
     * @return void
     */
    public function testOptionsAndTrue()
    {
        $this->checkHeaders(
            ['testheader1' => 'testvalue1', 'testheader2' => 'testvalue2'],
            ['testheader1 testvalue1', 'testheader2 testvalue2'],
            ['loggedin']
        );
    }

    /**
     * Test multiple options with no matching header
     *
     * @return void
     */
    public function testOptionsAndFalse()
    {
        $this->checkHeaders(
            ['testheader1' => 'testvalue1'],
            ['testheader1 testvalue1', 'testheader2 testvalue2'],
            []
        );
    }

    /**
     * Test option with multiple values and matching header
     *
     * @return void
     */
    public function testOptionValuesOrTrue()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue1'],
            ['testheader testvalue1 testvalue2'],
            ['loggedin']
        );
    }

    /**
     * Test option with multiple values and no matching header
     *
     * @return void
     */
    public function testOptionValuesOrFalse()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader testvalue1 testvalue2'],
            []
        );
    }

    /**
     * Test option with regex modifier and matching header
     *
     * @return void
     */
    public function testOptionRegexTrue()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader ~ ^testvalue$'],
            ['loggedin']
        );
    }

    /**
     * Test option with regex modifier and no matching header
     *
     * @return void
     */
    public function testOptionRegexFalse()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader ~ ^estvalue'],
            []
        );
    }

    /**
     * Test option with not modifier and matching header
     *
     * @return void
     */
    public function testOptionNotTrue()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader ! testval'],
            ['loggedin']
        );
    }

    /**
     * Test option with not modifier and no matching header
     *
     * @return void
     */
    public function testOptionNotFalse()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader ! testvalue'],
            []
        );
    }

    /**
     * Test option with not regex modifier and matching header
     *
     * @return void
     */
    public function testOptionNotRegexTrue()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader !~ testval$'],
            ['loggedin']
        );
    }

    /**
     * Test option with not regex modifier and no matching header
     *
     * @return void
     */
    public function testOptionNotRegexFalse()
    {
        $this->checkHeaders(
            ['testheader' => 'testvalue'],
            ['testheader !~ ^testvalue'],
            []
        );
    }

    /**
     * Setup request and header objects, run getPermissions and check the result
     *
     * @param array $headers        Request headers
     * @param mixed $options        options as from configuration
     * @param array $expectedResult expected result returned by getPermissions
     *
     * @return void
     */
    protected function checkHeaders($headers, $options, $expectedResult)
    {
        $request = new \Zend\Http\PhpEnvironment\Request();
        $request->setServer(new \Zend\Stdlib\Parameters($headers));
        $header = new Header($request);
        $result = $header->getPermissions($options);
        $this->assertEquals($result, $expectedResult);
    }
}
