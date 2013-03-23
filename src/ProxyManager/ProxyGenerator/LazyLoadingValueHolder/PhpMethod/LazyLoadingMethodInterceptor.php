<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ProxyManager\ProxyGenerator\LazyLoadingValueHolder\PhpMethod;

use CG\Generator\PhpMethod;
use CG\Generator\PhpProperty;
use ReflectionMethod;

/**
 * Method decorator for lazy loading value holder objects
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class LazyLoadingMethodInterceptor extends PhpMethod
{
    /**
     * @param ReflectionMethod $originalMethod
     * @param PhpProperty      $initializerProperty
     * @param PhpProperty      $valueHolderProperty
     *
     * @return LazyLoadingMethodInterceptor|static
     */
    public static function generateMethod(
        ReflectionMethod $originalMethod,
        PhpProperty $initializerProperty,
        PhpProperty $valueHolderProperty
    ) {
        /* @var $method self */
        $method            = static::fromReflection($originalMethod);
        $initializerName   = $initializerProperty->getName();
        $valueHolderName   = $valueHolderProperty->getName();
        /* @var $parameters \CG\Generator\PhpParameter[] */
        $parameters        = $originalMethod->getParameters();
        $methodName        = $originalMethod->getName();
        $initializerParams = array();
        $forwardedParams   = array();

        foreach ($parameters as $parameter) {
            $parameterName       = $parameter->getName();
            $initializerParams[] = var_export($parameterName, true) . ' => $' . $parameterName;
            $forwardedParams[]   = '$' . $parameterName;
        }

        $method->setBody(
            '$this->' . $initializerName
            . ' && $this->' . $initializerName
            . '->__invoke($this, $this->' . $valueHolderName . ', ' . var_export($methodName, true)
            . ', array(' . implode(', ', $initializerParams) . "));\n\n"
            . 'return $this->' . $valueHolderName . '->'
            . $methodName . '(' . implode(', ', $forwardedParams) . ');'
        );
        $method->setDocblock("/**\n * {@inheritDoc}\n */\n");

        return $method;
    }
}