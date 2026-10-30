# Draft ERD: Varenne

Draft database plan for Milestone 1. GitHub renders the Mermaid diagram below automatically.
It can also be pasted into https://mermaid.live to export a PNG for submission.

```mermaid
erDiagram
    USERS ||--o{ ADDRESSES : "has"
    USERS ||--o{ ORDERS : "places"
    USERS ||--o{ REVIEWS : "writes"
    USERS ||--o{ CART_ITEMS : "keeps in bag"
    USERS ||--o{ WISHLIST_ITEMS : "saves"
    CATEGORIES ||--o{ PRODUCTS : "groups"
    PRODUCTS ||--o{ PRODUCT_VARIANTS : "comes in"
    PRODUCTS ||--o{ REVIEWS : "receives"
    PRODUCTS ||--o{ WISHLIST_ITEMS : "appears in"
    PRODUCT_VARIANTS ||--o{ CART_ITEMS : "added as"
    PRODUCT_VARIANTS ||--o{ ORDER_ITEMS : "sold as"
    ORDERS ||--|{ ORDER_ITEMS : "contains"
    ADDRESSES ||--o{ ORDERS : "ships to"

    USERS {
        int id PK
        varchar first_name
        varchar last_name
        varchar email UK
        varchar password_hash
        varchar phone
        enum role "customer | admin"
        datetime created_at
    }
    ADDRESSES {
        int id PK
        int user_id FK
        varchar street
        varchar city
        varchar postal_code
        varchar country
        boolean is_default
    }
    CATEGORIES {
        int id PK
        varchar name UK
        varchar slug UK
        text description
    }
    PRODUCTS {
        int id PK
        int category_id FK
        varchar name
        text description
        decimal price
        varchar image_url
        boolean is_active
        datetime created_at
    }
    PRODUCT_VARIANTS {
        int id PK
        int product_id FK
        varchar size
        varchar color
        varchar sku UK
        int stock
    }
    CART_ITEMS {
        int id PK
        int user_id FK
        int variant_id FK
        int quantity
    }
    WISHLIST_ITEMS {
        int id PK
        int user_id FK
        int product_id FK
        datetime added_at
    }
    ORDERS {
        int id PK
        int user_id FK
        int address_id FK
        enum status "pending | processing | shipped | delivered | cancelled"
        decimal total
        varchar currency
        datetime created_at
    }
    ORDER_ITEMS {
        int id PK
        int order_id FK
        int variant_id FK
        int quantity
        decimal unit_price
    }
    REVIEWS {
        int id PK
        int user_id FK
        int product_id FK
        tinyint rating "1-5"
        text comment
        datetime created_at
    }
```

## Entities

| Entity | Why it exists | Where it shows in the SPA |
|---|---|---|
| `users` | Customers and admins (role column) | `#login`, `#register`, `#profile` |
| `addresses` | A user can save several shipping addresses | `#profile` (Addresses), `#cart` (Checkout) |
| `categories` | Women, Men, Accessories | `#home`, `#shop` filters, `#dashboard` |
| `products` | The garment itself (name, price, description) | `#shop`, `#product`, `#dashboard` |
| `product_variants` | Size and colour combinations with their own stock | `#product` size/colour pickers |
| `cart_items` | The user's bag, stored server side | `#cart` |
| `wishlist_items` | Saved pieces | `#profile` (Wishlist) |
| `orders` | A placed order with status and total | `#profile` (Order history), `#dashboard` |
| `order_items` | Lines of an order; `unit_price` is copied so later price changes don't alter old orders | `#dashboard` |
| `reviews` | Ratings and comments on products | `#product` (Client reviews) |

## Notes

- `order_items.unit_price` is stored on purpose (price at the time of purchase).
- Stock lives on `product_variants`, not `products`, because each size has its own stock.
- `orders.currency` prepares for the external API feature (currency conversion via an exchange-rate API).
- Relationships: one-to-many everywhere; the many-to-many between users and products is resolved by `cart_items`, `wishlist_items` and `reviews`.
