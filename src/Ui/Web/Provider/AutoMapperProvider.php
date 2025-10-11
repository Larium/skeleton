<?php

declare(strict_types=1);

namespace Larium\Ui\Web\Provider;

use DI\ContainerBuilder;

class AutoMapperProvider
{
    public function register(ContainerBuilder $builder): void
    {
        // Optional: Register object mappers used by Actions to map arrays / DTOs to domain DTOs
        // This is a placeholder implementation - you can expand this based on your mapping needs
        
        $builder->addDefinitions([
            // Example mapper registration (uncomment and implement as needed):
            // 'user.mapper' => function () {
            //     return new UserMapper();
            // },
            // 'product.mapper' => function () {
            //     return new ProductMapper();
            // },
        ]);
    }
}
