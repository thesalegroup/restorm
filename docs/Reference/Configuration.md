# Configuration

The only configuration format currently supported is yaml. Below is a reference
to the complete configuration currently available.

```YAML
connections:
    default:
        base_uri: https://example.com
        pagination_parameters:
            page_param: page
            per_page_param: per_page
entity_mappings:
    Project\Entity\Post:
        connection: default
        repository_class: TheSaleGroup\Restorm\EntityRepository
        paths:
            GET: /posts
            POST: /category/{{category.id}}/posts
        properties:
            id:
                identifier: true
                type: integer
            title:
                type: string
                unique: true
            content:
                type: string
```

# Connections

A connection describes the details of how to communicate with an API. A
connection can represent different website APIs or parts of a single one. All
entities, regardless of which connection they were found on, are managed by the
same entity manager and can reference an entity from any other connection.

The `connections` section contains a list of APIs you want to use with RESTORM,
each with a name that is referenced on an entity.


## base_uri
```YAML
base_uri: https://example.com
```

The URI of the endpoint. This URI must include a protocol, as well as any static
path prefixed before the REST endpoints. By allowing the path here, it may be
possible to define multiple connections to the different versions of the same
API, if the path allows for that. You could for example have something like this:

```YAML
connections:
    APIv1:
        base_uri: https://example.com/api/v1/
```

This could represent a connection to version 1 of an API which some entities may
need, whereas others may want to connection to version 2 of an API:

```YAML
connections:
    APIv1:
        base_uri: https://example.com/api/v2/
```


## Pagination Parameters
```YAML
pagination_parameters:
    page_param: page
    per_page_param: per_page
```

Pagination can be automatically handled if it's available via query parameters.
By giving details of pagination, a `PaginatedCollection` object can be returned
that is able to lazy load results from each page of a list while iterating.
`pagination_parameters` takes 2 additional pieces of information - the parameter
name for page number and the parameter name for the "items per page" count.