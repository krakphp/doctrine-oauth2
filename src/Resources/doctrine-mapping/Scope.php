<?php

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

$builder = new ClassMetadataBuilder($metadata);
$builder->setTable('oauth2_scopes');
$builder->createField('id', 'string')->isPrimaryKey()->build();
$builder->addField('name', 'string');
$builder->addField('description', 'text');
$builder->addField('createdAt', 'datetime');
