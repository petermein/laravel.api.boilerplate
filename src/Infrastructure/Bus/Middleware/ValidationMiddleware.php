<?php


namespace Api\Infrastructure\Bus\Middleware;


use Api\Infrastructure\Bus\Interfaces\QueryInterface;
use Api\Infrastructure\Bus\Interfaces\ValidatorInterface;
use Api\Infrastructure\Bus\Interfaces\ValidatorLocatorInterface;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class ValidationMiddleware implements MiddlewareInterface
{
    use LoggerAwareTrait;
    /**
     * @var Factory
     */
    private Factory $factory;
    /**
     * @var ValidatorLocatorInterface
     */
    private $validatorLocator;

    /**
     * ValidationMiddleware constructor.
     * @param Factory $factory
     * @param ValidatorLocatorInterface $validatorLocator
     */
    public function __construct(Factory $factory, ValidatorLocatorInterface $validatorLocator)
    {
        $this->factory = $factory;
        $this->validatorLocator = $validatorLocator;

        $this->logger = new NullLogger();
    }


    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var QueryInterface $query */
        $query = $envelope->getMessage();
        $context = [
            'message' => $query,
            'class' => \get_class($query),
        ];

        $validator = null;

        if ($envelope->all(ReceivedStamp::class)) {
            // it's a received message, do not send it back
            $this->logger->info('Validating message {class}', $context);
        } else {
            /**
             * @var string $alias
             * @var ValidatorInterface $validator
             */
            foreach ($this->validatorLocator->getValidators($envelope) as $alias => $validator) {
                $this->logger->info('Validating message {class} with {validator}',
                    $context + ['validator' => \get_class($validator)]);

                $factoryValidator = $this->factory->make(
                    $query->getData(),
                    $validator->rules(),
                    $validator->messages(),
                    $validator->customAttributes()
                );

                if ($factoryValidator->fails()) {
                    $this->logger->info('Validation failed {class} in {validator}',
                        $context + [
                            'validator' => \get_class($validator),
                            'errors' => $factoryValidator->getMessageBag()
                        ]);
                    throw new ValidationException($factoryValidator);
                }
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}