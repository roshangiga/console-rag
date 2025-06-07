<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Console Document Management System API - A comprehensive RAG (Retrieval-Augmented Generation) system for managing documents with hierarchical directory structure',
    title: 'Console API Documentation',
    contact: new OA\Contact(
        name: 'Mauritius Telecom Innovation Team',
        email: 'innovation@telecom.mu'
    )
)]
#[OA\Tag(
    name: 'Authentication',
    description: 'User authentication and authorization endpoints'
)]
#[OA\Tag(
    name: 'Directories',
    description: 'Directory management and navigation endpoints'
)]
#[OA\Tag(
    name: 'Documents',
    description: 'Document management, upload, download, and processing endpoints'
)]
#[OA\Server(
    url: '/api',
    description: 'Console API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class SwaggerController extends Controller
{
    // This controller serves as the base for OpenAPI documentation
}
