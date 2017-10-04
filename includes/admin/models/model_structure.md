# Model

## Data Structure
```
rop_data {
    services {
        [service]_[id] {
            id,
            service,
            credentials {

            },
            available_accounts { 
                [
                    id,
                    name,
                    account,
                    img,
                    active
                ] ...
            }
        }
    },
    active_accounts {
        [service]_[id]_[account_id] {
            service,
            user,
            account,
            img,
            created
        }
    }
}
```

## Accessors

- `get_authenticated_services()`
- `update_authenticated_services()`
- `get_active_accounts()`
- `update_active_accounts()`
- `find_service()`
- `find_account()`
