<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 */

namespace ZendTest\Filter;

use Zend\Filter\StaticFilter;
use Zend\Filter\FilterPluginManager;

/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @group      Zend_Filter
 */
class StaticFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Resets the default namespaces
     *
     * @return void
     */
    public function tearDown()
    {
        StaticFilter::setPluginManager(null);
    }

    public function testUsesFilterPluginManagerByDefault()
    {
        $plugins = StaticFilter::getPluginManager();
        $this->assertInstanceOf('Zend\Filter\FilterPluginManager', $plugins);
    }

    public function testCanSpecifyCustomPluginManager()
    {
        $plugins = new FilterPluginManager();
        StaticFilter::setPluginManager($plugins);
        $this->assertSame($plugins, StaticFilter::getPluginManager());
    }

    public function testCanResetPluginManagerByPassingNull()
    {
        $plugins = new FilterPluginManager();
        StaticFilter::setPluginManager($plugins);
        $this->assertSame($plugins, StaticFilter::getPluginManager());
        StaticFilter::setPluginManager(null);
        $registered = StaticFilter::getPluginManager();
        $this->assertNotSame($plugins, $registered);
        $this->assertInstanceOf('Zend\Filter\FilterPluginManager', $registered);
    }

    /**
     * Ensures that we can call the static method execute()
     * to instantiate a named validator by its class basename
     * and it returns the result of filter() with the input.
     */
    public function testStaticFactory()
    {
        $filteredValue = StaticFilter::execute('1a2b3c4d', 'Digits');
        $this->assertEquals('1234', $filteredValue);
    }

    /**
     * Ensures that a validator with constructor arguments can be called
     * with the static method get().
     */
    public function testStaticFactoryWithConstructorArguments()
    {
        // Test HtmlEntities with one ctor argument.
        $filteredValue = StaticFilter::execute('"O\'Reilly"', 'HtmlEntities', array('quotestyle' => ENT_COMPAT));
        $this->assertEquals('&quot;O\'Reilly&quot;', $filteredValue);

        // Test HtmlEntities with a different ctor argument,
        // and make sure it gives the correct response
        // so we know it passed the arg to the ctor.
        $filteredValue = StaticFilter::execute('"O\'Reilly"', 'HtmlEntities', array('quotestyle' => ENT_QUOTES));
        $this->assertEquals('&quot;O&#039;Reilly&quot;', $filteredValue);
    }

    /**
     * Ensures that if we specify a validator class basename that doesn't
     * exist in the namespace, get() throws an exception.
     *
     * Refactored to conform with ZF-2724.
     *
     * @group  ZF-2724
     */
    public function testStaticFactoryClassNotFound()
    {
        $this->setExpectedException('Zend\ServiceManager\Exception\ExceptionInterface');
        StaticFilter::execute('1234', 'UnknownFilter');
    }

    public function testUsesDifferentConfigurationOnEachRequest()
    {
        $first = StaticFilter::execute('foo', 'callback', array(
            'callback' => function ($value) {
                return 'FOO';
            },
        ));
        $second = StaticFilter::execute('foo', 'callback', array(
            'callback' => function ($value) {
                return 'BAR';
            },
        ));
        $this->assertNotSame($first, $second);
        $this->assertEquals('FOO', $first);
        $this->assertEquals('BAR', $second);
    }
}
