# Rate Limiter

This is a very basic rate limiting framework for PHP.  It stores hits in a session variable. 
It's not incredibly efficient, but does what it needs to for now.  Happy to take pull / merges and attribute changes. 
It was built as one way to restrict spamming of a form.  Low profile project until we have more use needs for it.

It has the basic support for new drivers, such as database, redis, etc, but hasn't been built out due to current needs.

If it actually gets usage, we will work on better documentation, but this should work for now.  Look at Limiter for all 
available methods.

## Usage

```
use O2Group\RateLimit\Limiter;

// Set how many seconds until rates expire.
Limiter::setExpiration(3600);

/**
 * Every possible implementation needs a key.  
 * For example, if you were using it for a login, maybe name it login.
 */
$rateKey = 'login';

/**
 * Then when someone submits the form:
 * 1) Increment the counter.
 * 2) Check if they took too many attempts, failing if necessary.
 * 3) Process the login.
 * 4) Clear the counter or let it timeout naturally.
 */

Limiter::increment($key);

$maxAttempts = 10;
if (Limiter::tooManyAttempts($key, $maxAttempts)) {
    echo 'Not today!';
    exit;
}

// Attempt Login

// Clear if valid.
Limiter::forget($key);
```

### Roadmap
* Work on our Adapter interface.  It was copied from another project and just adjusted to meet our needs.  
* Add in exceptions on the adapter instead of returning nulls.  We should be checking for existance, not just getting and hoping.
* Add a method to get the entire adapter.
* Make adapter type configurable.
* Code clean up and commenting.

Author: [Chase C. Miller](https://github.com/chasecmiller "Chase Miller on GitHub")