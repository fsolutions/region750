export const HOST_FOR_STORAGE = '/download/'
export const API_LIST_OF_ROLES = '/api/constants/roles'

export const API_LOGIN = '/api/login'
export const API_REGISTRATION = '/api/register'
export const API_GET_USER = '/api/get-user'

export const API_USERS = '/api/users'
export const API_USERS_LISTS = '/api/users/lists'                                               // {list-type} - for-accountants, etc

export const API_ORDERS = '/api/orders'
export const API_ORDERS_UPDATE_EASY = '/api/orders/update-easy'
export const API_PRESCRIPTIONS = '/api/prescriptions'

export const API_REFERENCE = '/api/references'                                                  // can take reference_id as reference_parent_id
export const API_REFERENCE_PROPERTIES = '/api/references/properties'                            // can take reference_id

export const API_DADATA_COMPANY = '/api/dadata/company'
export const API_DADATA_ADDRESS = '/api/dadata/address'

export const API_GET_NOTIFICATIONS_ALL = '/api/notifications'
export const API_GET_NOTIFICATIONS_UNREAD = '/api/notifications/unread'
export const API_POST_NOTIFICATIONS_READ = '/api/notifications/read'                            // /{$id}
export const API_POST_NOTIFICATIONS_ORDERS_READ = '/api/notifications/read-all-orders'          // can take "place" parameter
export const API_POST_NOTIFICATIONS_READ_ALL = '/api/notifications/read-all'
export const API_DELETE_NOTIFICATION = '/api/notifications/delete'                              // /{$id}
export const API_POST_NOTIFICATION_ALL = '/api/notifications/delete-all'

export const API_LOG_HISTORY = '/api/logs'
export const API_HISTORY = '/api/history'

export const API_CONTRACTS = '/api/contracts'
export const API_CONTRACTS_TO = '/api/contracts-to'

export const API_CALENDAR = '/api/calendar'

export const API_GET_PASSWORD_CODE = '/api/get-password'
export const API_POST_SEND_SMS = '/api/send-sms'

export const API_FLATS = '/api/addresses/flats'
export const API_HOUSES = '/api/addresses/houses'
export const API_STREETS = '/api/addresses/streets'
export const API_CITIES = '/api/addresses/cities'
export const API_REGIONS = '/api/addresses/regions'

export const API_EQUIPMENT = '/api/equipment'
export const API_TO_VENTILATION = '/api/to-ventilation'
export const API_TO_VDGO = '/api/to-vdgo'