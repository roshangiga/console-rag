<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'Console Document Management System API - A comprehensive RAG (Retrieval-Augmented Generation) system for managing documents with hierarchical directory structure',
    title: 'Console API Documentation',
    contact: new OA\Contact(
        name: 'Mauritius Telecom Innovation Team',
        email: 'innovation@maurtiustelecom.mu'
    )
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
