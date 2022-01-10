module.exports = {
    defaults: {},
    get: jest.fn(url => {
        // console.log(url);
        if (url == 'app/token') {
            return Promise.resolve({
                data: { token: 'testToken' },
            });
        } else if (url == 'app/me') {
            return Promise.resolve({
                data: {
                    id: '5430c6a5-8958-4868-acc7-a6cdf0554e05',
                    name: 'Site Admin',
                    email: 'admin@tecdiary.com',
                    username: 'admin',
                    locale: 'en',
                    phone: null,
                    email_verified_at: null,
                    active: 1,
                    location_id: null,
                    settings: [],
                    media: [],
                    roles: [
                        {
                            id: '19b4e326-4d35-48b8-a740-84633b56e3f7',
                            name: 'admin',
                        },
                    ],
                    locations: [],
                },
            });
        }
        return Promise.resolve({
            status: 200,
            response: { data: { success: true } },
        });
    }),
    post: jest.fn(url => {
        // console.log(url);
        // if (url === '/something') {}
        return Promise.resolve({
            status: 200,
            response: { data: { success: true } },
        });
    }),
    create: jest.fn(function() {
        return this;
    }),
};
