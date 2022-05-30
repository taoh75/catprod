# catprod
Prueba de Código, Optime Consulting, Symfony

## Entidades del Proyecto 

** Categorías de Productos ** 

  Entidad: Category 

  Atributos:

    name      : string PK
    active    : boolean
    createdAt : datetime
    updatedAt : datetime
    
** Definición de Productos ** 

  Entidad: Products 

  Atributos

    code        : string
    name        : string
    description : string
    brand       : string 
    category    : string FK [Category:name]
    price       : float
    createdAt   : datetime
    updatedAt   : datetime
  
  
