Only add helpers that:
* contain SMALL static functions with general purpose
* always return the same result
* NEVER modify data (except for what they return or reference params)
* NEVER read or write from DB or filesystem (except for logging purposes) or any other persistence storage
