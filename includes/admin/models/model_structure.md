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

## Tests
Facebook
```
'app_id' => '470293890022208', 
'secret' => 'bf3ee9335692fee071c1a41fbe52fdf5'
```

LinkedIn
```
'client_id' => '781biqyg6fhkam', 
'secret' => 'K1o5S03jnSDt11w8'
```

Tumblr
```
'consumer_key' => 'oN3jqKF0VLW0BdpAMbbkL2PYtkpnePYxaRYf8rbX4R5SEnBbGW',
'consumer_secret' => 'fQxyywKZJMc474SxVfZCYbrIXARnJS6DTYJoyiQ6sbWFvuM4Di'
```