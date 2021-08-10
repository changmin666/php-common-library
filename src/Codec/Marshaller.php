<?php
declare (strict_types=1);

namespace PXCommon\Codec;


use PXCommon\StringUtil\StringUtil;
use ReflectionClass;
use ReflectionException;

class Marshaller
{
    const SINGLE_TYPE = ['int', 'integer', 'bool', 'boolean', 'string', 'float', 'double', 'long', 'array', 'resource'];
    private string $_annotationName = '';

    public function __construct($_annotationName)
    {
        $this->_annotationName = $_annotationName;
    }

    /**
     * 序列化
     * @param object $obj
     * @return array
     * @throws ReflectionException
     */
    public function marshal(object $obj): array
    {
        return $this->_marshal($obj);
    }

    /**
     * 序列化
     * @param array $row
     * @param string $refClass
     * @return object
     * @throws ReflectionException
     */
    public function unmarshal(array $row, string $refClass): object
    {
        return $this->_unmarshal($row, $refClass);
    }

    /**
     * 序列化
     * @param object $obj
     * @return array
     * @throws ReflectionException
     */
    private function _marshal(object $obj): array
    {
        try {
            // 获取类名
            $refClass = get_class($obj);
            // 获取反射类
            $ref = new ReflectionClass($refClass);
            // 对象示例
            $instance = $obj;
            // 该对象属性
            $properties = $ref->getProperties();
            // 父类属性处理
            $parentResult = $this->marshalParent($instance, $ref);
            // 当前类处理
            $result = $this->readPropertiesToArray($instance, $properties);
            // merge result
            $result = array_merge($parentResult, $result);
        } catch (ReflectionException $e) {
            throw $e;
        }
        return $result;
    }

    /**
     * 反序列化
     * @param array $row
     * @param string $refClass
     * @return object
     * @throws ReflectionException
     */
    public function _unmarshal(array $row, string $refClass): object
    {
        try {
            $columns = [];
            // 获取反射类
            $ref = new ReflectionClass($refClass);
            // 创建对象实例
            $instance = $ref->newInstance();
            // 读取所有类属性
            $properties = $ref->getProperties();
            foreach ($properties as $property) {
                // 获取注解
                $annotation = $this->getAnnotation($property->getDocComment() ?: '');
                if ($annotation) {
                    $columns[$annotation] = $property->getName();
                }
            }
            // 读取数组所有元素
            foreach ($row as $key => $value) {
                $attributeName = $columns[$key] ?? false;
                if ($attributeName) {
                    // 获取属性
                    $property = $ref->getProperty($attributeName);
                    $property->setAccessible(true);
                    // 自定义类型处理
                    if (!in_array($property->getType(), self::SINGLE_TYPE)) {
                        $className = (string)$property->getType();
                        $value = $this->_unmarshal($value, $className);
                    }
                    $property->setValue($instance, $value);
                } else {
                    // 无匹配注解，获取类属性，小写驼峰命名
                    $property = null;
                    try {
                        if (is_string($key)) {
                            $property = $ref->getProperty(StringUtil::toCamelize($key));
                        }
                    } catch (ReflectionException $e) {
                        $property = null;
                    }
                    if ($property) {
                        $annotation = $this->getAnnotation($property->getDocComment() ?: '');
                        // 排除跳过反序列化
                        if ($annotation && $annotation == '-') {
                            continue;
                        }
                        $property->setAccessible(true);
                        // 自定义类型处理
                        if (!in_array($property->getType(), self::SINGLE_TYPE)) {
                            $className = (string)$property->getType();
                            $value = self::unmarshal($value, $className);
                        }
                        $property->setValue($instance, $value);
                    }
                }
            }
            return $instance;
        } catch (ReflectionException $e) {
            throw $e;
        }
    }

    /**
     * 父类属性
     * @param object $instance
     * @param ReflectionClass $refClass
     * @return array
     * @throws ReflectionException
     */
    private function marshalParent(object $instance, ReflectionClass $refClass): array
    {
        // 循环递归处理所有父类索性
        $result = [];
        $parent = $refClass->getParentClass();
        while ($parent) {
            $properties = $parent->getProperties();
            $result = $this->readPropertiesToArray($instance, $properties);
            $parent = $parent->getParentClass();
        }
        return $result;
    }

    /**
     * 读取类属性、注解转为数组对应key->value
     * @param object $instance
     * @param mixed $properties
     * @return array
     * @throws ReflectionException
     */
    private function readPropertiesToArray(object $instance, mixed $properties): array
    {
        $result = [];
        // 读出所有类属性
        foreach ($properties as $property) {
            // 获取注解
            $annotation = $this->getAnnotation($property->getDocComment() ?: '');
            // 设置类属性为可访问
            $property->setAccessible(true);
            // 存在注解
            if ($annotation && $annotation != '-') {
                // 自定义类型处理
                if (!in_array($property->getType(), self::SINGLE_TYPE)) {
                    $value = $this->_marshal($property->getValue($instance));
                    $result[$annotation] = $value;
                } else {
                    if ($property->getType() == 'array') {
                        // 数组类型处理
                        $array = $property->getValue($instance);
                        foreach ($array as $k => $v) {
                            if (!in_array(gettype($v), self::SINGLE_TYPE)) {
                                // 数组内元素为自定义类型
                                $value = $this->_marshal($v);
                                $result[$annotation][$k] = $value;
                            } else {
                                $result[$annotation][$k] = $v;
                            }
                        }
                    } else {
                        // 普通类型处理
                        $result[$annotation] = $property->getValue($instance);
                    }
                }
            } else {
                // 跳过序列化
                if ($annotation == '-') {
                    continue;
                }
                // 没有任何注解处理，驼峰小写转下划线分隔
                $annotation = StringUtil::toUnCamelize($property->getName());
                if (!in_array($property->getType(), self::SINGLE_TYPE)) {
                    // 自定义类型处理
                    $value = $this->_marshal($property->getValue($instance));
                    $result[$annotation] = $value;
                } else {
                    if ($property->getType() == 'array') {
                        // 数组类型处理
                        $array = $property->getValue($instance);
                        foreach ($array as $k => $v) {
                            if (!in_array(gettype($v), self::SINGLE_TYPE)) {
                                $value = $this->_marshal($v);
                                $result[$annotation][$k] = $value;
                            } else {
                                $result[$annotation][$k] = $v;
                            }
                        }
                    } else {
                        // 简单类型处理
                        $result[$annotation] = $property->getValue($instance);
                    }
                }
            }

        }
        return $result;
    }


    /**
     * @param string $docComment
     * @return string|null
     */
    private function getAnnotation(string $docComment): ?string
    {
        // 替换所有空格
        $annotation = preg_replace('# #', '', $docComment);
        // 替换单引号成双引号
        $annotation = str_replace('\'', '"', $annotation);
        // 匹配格式 @Json("*")
        match ($this->_annotationName) {
            'Column' => preg_match('/@Column+\(\"(.+?)\"\)/', $annotation, $matches),
            'Redis' => preg_match('/@Redis+\(\"(.+?)\"\)/', $annotation, $matches),
            'Json' => preg_match('/@Json+\(\"(.+?)\"\)/', $annotation, $matches),
        };
        if (count($matches) == 2) {
            return $matches[1];
        }
        return null;
    }

}