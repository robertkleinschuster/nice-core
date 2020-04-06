<?php
/**
 * @see       https://github.com/niceshops-com/CoreComponents for the canonical source repository
 * @license   https://github.com/niceshops-com/CoreComponents/blob/master/LICENSE BSD 3-Clause License
 */

namespace NiceshopsCom\CoreComponents\Traits;

use NiceshopsCom\CoreComponents\AttributeAwareInterface;
use NiceshopsCom\CoreComponents\Exception;
use NiceshopsCom\CoreComponents\StrictAttributeAwareInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

/**
 * Class AttributeTraitTest
 * @coversDefaultClass AttributeTrait
 * @uses               \NiceshopsCom\CoreComponents\Traits\AttributeTrait
 * @package            Niceshops\Library\Core\Traits
 */
class AttributeTraitTest extends TestCase
{
    
    
    /**
     * @var AttributeTrait|MockObject
     */
    protected $object;
    
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->getMockBuilder(AttributeTrait::class)->getMockForTrait();
    }
    
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    
    /**
     * @return array    ( <KEY>, <EXPECTED_KEY> )
     */
    public function normalizeAttributeKeyDataProvider()
    {
        return [
            ["foo", "foo"],
            [" foo ", "foo"],
            ["FOO", "foo"],
            ["Foo", "foo"],
            ["fooBar", "foo_bar"],
            ["FOOBar", "foo_bar"],
            ["fooBAR", "foo_bar"],
            ["foo_Bar", "foo_bar"],
            ["foo_BAR", "foo_bar"],
            ["foo_bar", "foo_bar"],
            ["foo_barBaz", "foo_bar_baz"],
            ["fooBar_Baz", "foo_bar_baz"],
            ["foobar_Baz", "foobar_baz"],
            ["0", "0"],
            ["0foo", "0foo"],
            ["0Foo", "0_foo"],
            ["foo1", "foo1"],
            ["FOO1", "foo1"],
            ["__foo", "__foo"],
            ["__foo__", "__foo__"],
            ["__FooBar__", "__foo_bar__"],
        ];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider normalizeAttributeKeyDataProvider
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::normalizeAttributeKey()
     *
     * @param string $key
     * @param string $expectedKey
     *
     * @throws ReflectionException
     */
    public function testNormalizeAttributeKey(string $key, string $expectedKey)
    {
        $method = new ReflectionMethod(get_class($this->object), "normalizeAttributeKey");
        $method->setAccessible(true);
        
        $this->assertSame($expectedKey, $method->invoke($this->object, $key));
    }
    
    
    /**
     * format: ( <ARRAY_ATTRIBUTES>, <MAP_EXPECTED_VALUES>, <ARRAY_EXPECTED_EXCEPTION> )
     *
     * <ARRAY_ATTRIBUTES>           at least one of array( "key" => <ATTRIBUTE_KEY>, "value" => <ATTRIBUTE_VALUE> )
     * <MAP_EXPECTED_VALUES>        keys are <ATTRIBUTE_KEY>s, values are <ATTRIBUTE_VALUE>s
     * <ARRAY_EXPECTED_EXCEPTION>   0 => <EXPECTED_EXCEPTION>, 1 => <EXPECTED_MESSAGE> | <EXPETECTED_MESSAGE_REG_EXP>
     *
     * @return array
     */
    public function getSetUnsetDataProvider()
    {
        return [
            [
                [
                    ["key" => "foo", "value" => "bar"],
                    ["key" => "bar", "value" => "baz"],
                ],
                [
                    "foo" => "bar",
                    "bar" => "baz",
                ],
                null,
            ],
            
            [
                [
                    ["key" => "null", "value" => null],
                    ["key" => "number", "value" => 123],
                    ["key" => "string", "value" => "123"],
                ],
                [
                    "null" => null,
                    "number" => 123,
                    "string" => "123",
                ],
                null,
            ],
            
            [
                [
                    ["key" => "foo", "value" => "bar"],
                    ["key" => "foo", "value" => "bar2"],
                ],
                [
                    "foo" => "bar2",
                ],
                null,
            ],
            
            [
                [
                    ["key" => "fooBar", "value" => "bar"],
                ],
                [
                    "fooBar" => "bar",
                    "foo_bar" => "bar",
                    "Foo_Bar" => "bar",
                    "FOO_BAR" => "bar",
                    "foobar" => null,
                ],
                null,
            ],
            
            [
                [
                    ["key" => 123, "value" => "bar"],
                    ["key" => "123", "value" => "bar2"],
                ],
                [
                    "123" => "bar2",
                ],
                null,
            ],
            
            [
                [
                    ["key" => true, "value" => "bar"],
                ],
                [
                    "1" => "bar",
                    "true" => null,
                ],
                null,
            ],
            
            [
                [
                    ["key" => false, "value" => "bar"],
                ],
                [
                    "" => "bar",
                    " " => "bar",
                    "false" => null,
                    "0" => null,
                ],
                null,
            ],
            
            [
                [
                    ["key" => null, "value" => "bar"],
                ],
                [],
                ["Error"],
            ],
            
            [
                [
                    ["key" => [], "value" => "bar"],
                ],
                [],
                ["Error"],
            ],
            
            [
                [
                    ["key" => new stdClass(), "value" => "bar"],
                ],
                [],
                ["Error"],
            ],
            
            [
                [
                    ["key" => "foo", "value" => "bar"],
                    ["key" => "FOO", "value" => "bar2"],
                ],
                [
                    "foo" => "bar2",
                ],
                [ Exception::class, "#Try to set the attribute.*#" ]
            ],
        ];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider getSetUnsetDataProvider
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute()
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::setAttribute()
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::unsetAttribute()
     *
     * @param array $arrAttribute
     * @param array $arrExpectedValue
     *
     * @param null  $arrException
     *
     * @throws Exception
     */
    public function testGetSetUnsetAttribute(array $arrAttribute, array $arrExpectedValue, $arrException = null)
    {
        if ($arrException) {
            $this->expectException($arrException[0]);
            if (!empty($arrException[1])) {
                if (substr($arrException[1], 0, 1) === substr($arrException[1], -1)) {
                    $this->expectExceptionMessageRegExp($arrException[1]);
                } else {
                    $this->expectExceptionMessage($arrException[1]);
                }
            }
        }
        
        foreach ($arrAttribute as $arrVal) {
            $this->object->setAttribute($arrVal["key"], $arrVal["value"]);
        }
        
        foreach ($arrExpectedValue as $key => $expectedValue) {
            $curValue = $this->object->getAttribute($key);
            if ($curValue === null) {
                $messageValue = "NULL";
            } else {
                $messageValue = "'$curValue'";
            }
            
            $this->assertSame(
                $expectedValue, $curValue, "Attribute '$key': value $messageValue doesn't match the expected value '$expectedValue'!"
            );
        }
        
        foreach ($arrAttribute as $arrVal) {
            $key = $arrVal["key"];
            $this->object->unsetAttribute($key);
            $this->assertNull($this->object->getAttribute($key));
        }
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsCom\CoreComponents\Traits\AttributeTrait::lockAttribute()
     * @covers \NiceshopsCom\CoreComponents\Traits\AttributeTrait::unlockAttribute()
     * @throws Exception
     * @uses   \NiceshopsCom\CoreComponents\Traits\AttributeTrait::setAttribute()
     * @uses   \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute()
     */
    public function testLockUnlockAttribute()
    {
        $this->assertNull($this->object->getAttribute("foo"));
        
        $this->object->setAttribute("foo", "bar");
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        
        $this->object->lockAttribute("foo");
        $this->object->setAttribute("foo", "baz");
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        
        $this->object->unlockAttribute("foo");
        $this->object->setAttribute("foo", "baz");
        $this->assertSame("baz", $this->object->getAttribute("foo"));
    }
    
    
    /**
     * format: ( <ARRAY_ATTRIBUTES>, <MAP_EXPECTED_VALUES>, <ARRAY_EXPECTED_EXCEPTION> )
     *
     * <ARRAY_ATTRIBUTES>           at least one of array( "key" => <ATTRIBUTE_KEY>, "value" => <ATTRIBUTE_VALUE> )
     * <MAP_EXPECTED_VALUES>        keys are <ATTRIBUTE_KEY>s, values are <ATTRIBUTE_VALUE>s
     *
     * @return array
     */
    public function getAttributeListDataProvider()
    {
        return [
            [
                [
                    ["key" => "foo", "value" => "bar"],
                    ["key" => "bar", "value" => "baz"],
                ],
                [
                    "foo" => "bar",
                    "bar" => "baz",
                ],
            ],
            [
                [
                    ["key" => "foo", "value" => "bar"],
                    ["key" => "bar", "value" => "baz"],
                    ["key" => "bar", "value" => "bat"],
                ],
                [
                    "foo" => "bar",
                    "bar" => "bat",
                ],
            ],
            [
                [
                    ["key" => "bar", "value" => "baz"],
                    ["key" => "BAR", "value" => "bat"],
                ],
                [
                    "bar" => "baz",
                ],
                Exception::class
            ],
            [
                [],
                [],
            ],
        
        ];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider getAttributeListDataProvider
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute_List()
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttributes()
     *
     * @param array       $arrAttribute
     * @param array       $expectedValue
     *
     * @param string|null $expectecException
     *
     * @throws Exception
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::setAttribute()
     */
    public function testGetAttributeList(array $arrAttribute, array $expectedValue, string $expectecException = null)
    {
        if ($expectecException) {
            $this->expectException($expectecException);
        }
        foreach ($arrAttribute as $arrVal) {
            $this->object->setAttribute($arrVal["key"], $arrVal["value"]);
        }
        
        $arrDiff = array_diff_assoc($this->object->getAttribute_List(), $expectedValue);
        $this->assertCount(0, $arrDiff, "Attributes '" . implode("', '", array_keys($arrDiff)) . "' are different!");
        
        $arrDiff = array_diff_assoc($expectedValue, $this->object->getAttribute_List());
        $this->assertCount(0, $arrDiff, "Attributes '" . implode("', '", array_keys($arrDiff)) . "' not found!");
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::__call()
     * @throws ReflectionException
     * @throws Exception
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute()
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testMagicAttributeAccess_with_Call()
    {
        $allowMagicGetAttribute = new ReflectionProperty(get_class($this->object), "allowMagicGetAttribute");
        $allowMagicGetAttribute->setAccessible(true);
        
        $allowMagicSetAttribute = new ReflectionProperty(get_class($this->object), "allowMagicSetAttribute");
        $allowMagicSetAttribute->setAccessible(true);
        
        $allowMagicGetAttribute->setValue($this->object, true);
        $allowMagicSetAttribute->setValue($this->object, true);
        
        $this->object->setFoo("bar");
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        $this->assertSame("bar", $this->object->getFoo());
        
        $allowMagicGetAttribute->setValue($this->object, false);
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        $this->assertSame(null, $this->object->getFoo());
        
        
        $allowMagicSetAttribute->setValue($this->object, false);
        $this->object->setFoo("baz");
        $this->assertSame("bar", $this->object->getAttribute("foo"));
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::__get()
     * @throws Exception
     * @throws ReflectionException
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute()
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::setAttribute()
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testMagicAttributeAccess_with_Get()
    {
        $allowMagicGetAttribute = new ReflectionProperty(get_class($this->object), "allowMagicGetAttribute");
        $allowMagicGetAttribute->setAccessible(true);
        
        $allowMagicGetAttribute->setValue($this->object, true);
        $this->object->setAttribute("foo", "bar");
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        $this->assertSame("bar", $this->object->foo);
        
        $allowMagicGetAttribute->setValue($this->object, false);
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        $this->assertSame(null, $this->object->foo);
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::__set()
     * @throws Exception
     * @throws ReflectionException
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute()
     * @noinspection PhpUndefinedFieldInspection
     */
    public function testMagicAttributeAccess_with_Set()
    {
        $allowMagicSetAttribute = new ReflectionProperty(get_class($this->object), "allowMagicSetAttribute");
        $allowMagicSetAttribute->setAccessible(true);
        
        $allowMagicSetAttribute->setValue($this->object, true);
        $this->object->foo = "bar";
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        
        $allowMagicSetAttribute->setValue($this->object, false);
        $this->object->foo = "baz";
        $this->assertSame("bar", $this->object->getAttribute("foo"));
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::__set()
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::__get()
     * @covers       \NiceshopsCom\CoreComponents\Traits\AttributeTrait::__call()
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::setAttribute()
     * @uses         \NiceshopsCom\CoreComponents\Traits\AttributeTrait::getAttribute()
     * @noinspection PhpUndefinedFieldInspection
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testMagicAttributeAccess()
    {
        $magic = new class {
            use AttributeTrait;
            
            
            /**
             *  constructor.
             */
            public function __construct()
            {
                $this->allowMagicGetAttribute = true;
                $this->allowMagicSetAttribute = true;
            }
        };
        
        $notMagic = new class {
            use AttributeTrait;
            
            
            /**
             *  constructor.
             */
            public function __construct()
            {
                $this->allowMagicGetAttribute = false;
                $this->allowMagicSetAttribute = false;
            }
        };
        
        
        $this->assertNull($magic->foo);
        $this->assertNull($notMagic->foo);
        
        $magic->foo = "bar";
        $notMagic->foo = "bar";
        $this->assertSame("bar", $magic->foo);
        $this->assertSame("bar", $magic->getFoo());
        $this->assertNull($notMagic->foo);
        $this->assertNull($notMagic->getFoo());
        
        $magic->setFoo("baz");
        $notMagic->setFoo("baz");
        $this->assertSame("baz", $magic->foo);
        $this->assertSame("baz", $magic->getFoo());
        $this->assertNull($notMagic->foo);
        $this->assertNull($notMagic->getFoo());
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsCom\CoreComponents\Traits\AttributeTrait::hasAttribute()
     * @throws Exception
     * @uses   \NiceshopsCom\CoreComponents\Traits\AttributeTrait::setAttribute()
     */
    public function testHasAttribute()
    {
        $this->assertFalse($this->object->hasAttribute("foo"));
        
        $this->object->setAttribute("foo", "bar");
        $this->assertTrue($this->object->hasAttribute("foo"));
        $this->assertTrue($this->object->hasAttribute("FOO"));
        $this->assertTrue($this->object->hasAttribute("Foo"));
        $this->assertFalse($this->object->hasAttribute("bar"));
    }
    
    
    /**
     * @group  unit
     * @small
     *
     * @covers \NiceshopsCom\CoreComponents\AttributeAwareInterface::setAttribute()
     * @covers \NiceshopsCom\CoreComponents\AttributeAwareInterface::hasAttribute()
     * @covers \NiceshopsCom\CoreComponents\AttributeAwareInterface::getAttribute()
     * @covers \NiceshopsCom\CoreComponents\AttributeAwareInterface::unsetAttribute()
     * @covers \NiceshopsCom\CoreComponents\AttributeAwareInterface::getAttribute_List()
     * @throws Exception
     */
    public function testAttributeAwareInterface()
    {
        $this->object = new class implements AttributeAwareInterface {
            use AttributeTrait;
        };
        
        $this->assertSame([], $this->object->getAttribute_List());
        
        $this->object->setAttribute("foo", "bar");
        $this->object->setAttribute("bar", "baz");
        $this->object->setAttribute("baz", "bat");
        
        $this->assertCount(3, $this->object->getAttribute_List());
        $this->assertSame(["foo" => "bar", "bar" => "baz", "baz" => "bat"], $this->object->getAttribute_List());
        
        $this->assertTrue($this->object->hasAttribute("foo"));
        $this->assertTrue($this->object->hasAttribute("bar"));
        $this->assertTrue($this->object->hasAttribute("baz"));
        $this->assertFalse($this->object->hasAttribute("bat"));
        
        $this->assertSame("bar", $this->object->getAttribute("foo"));
        $this->assertSame("baz", $this->object->getAttribute("bar"));
        $this->assertSame("bat", $this->object->getAttribute("baz"));
        $this->assertSame(null, $this->object->getAttribute("bat"));
        
        
        $this->object->unsetAttribute("foo");
        
        $this->assertCount(2, $this->object->getAttribute_List());
        $this->assertSame(["bar" => "baz", "baz" => "bat"], $this->object->getAttribute_List());
        
        $this->assertFalse($this->object->hasAttribute("foo"));
        $this->assertTrue($this->object->hasAttribute("bar"));
        $this->assertTrue($this->object->hasAttribute("baz"));
        $this->assertFalse($this->object->hasAttribute("bat"));
        
        $this->assertSame(null, $this->object->getAttribute("foo"));
        $this->assertSame("baz", $this->object->getAttribute("bar"));
        $this->assertSame("bat", $this->object->getAttribute("baz"));
        $this->assertSame(null, $this->object->getAttribute("bat"));
    }
    
    
    /**
     * @return array
     */
    public function strictAttributeAwareInterfaceDataProvider()
    {
        return [
            [
                ["foo" => "bar"],
                ["foo" => "bar"],
            ],
            [
                ["foo" => null],
                ["foo" => null],
            ],
            [
                [],
                ["foo" => null],
                Exception::class,
            ],
            [
                ["foo" => "bar"],
                ["bar" => null],
                Exception::class,
            ],
        ];
    }
    
    
    /**
     * @group        unit
     * @small
     *
     * @dataProvider strictAttributeAwareInterfaceDataProvider
     *
     * @covers       \NiceshopsCom\CoreComponents\StrictAttributeAwareInterface::setAttribute()
     * @covers       \NiceshopsCom\CoreComponents\StrictAttributeAwareInterface::getAttribute()
     * @covers       \NiceshopsCom\CoreComponents\StrictAttributeAwareInterface::hasAttribute()
     *
     * @param array       $arrAttribute
     * @param array       $arrExpectedValue
     * @param string|null $expectedException
     *
     * @throws Exception
     */
    public function testStrictAttributeAwareInterface(array $arrAttribute, array $arrExpectedValue, string $expectedException = null)
    {
        $this->object = new class implements StrictAttributeAwareInterface {
            use AttributeTrait;
        };
        
        foreach ($arrExpectedValue as $key => $expectedValue) {
            $this->assertSame(false, $this->object->hasAttribute($key), "attribute: $key");
        }
        
        if ($expectedException) {
            $this->expectException(Exception::class);
        }
        
        foreach ($arrAttribute as $key => $value) {
            $this->object->setAttribute($key, $value);
        }
        
        
        foreach ($arrExpectedValue as $key => $expectedValue) {
            $this->assertSame($expectedValue, $this->object->getAttribute($key), "attribute: $key");
        }
    }
}