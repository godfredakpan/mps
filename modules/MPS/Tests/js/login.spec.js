import Vuex from 'vuex';
import ViewUI from 'view-design';
import axios from './mocks/axios';
import store from '@mpsjs/store/index';
import { config, mount, createLocalVue, RouterLinkStub } from '@vue/test-utils';

import LoginComponent from '@mpscom/LoginComponent';

const localVue = createLocalVue();
localVue.use(Vuex);
localVue.use(ViewUI);

import Filters from '@mpsjs/core/Filters';
for (const [key, value] of Object.entries(Filters)) {
    localVue.filter(key, value);
}

const $route = {
    path: '/login',
    query: { reset: null },
};

describe('Login', () => {
    test('Login should be working as expected', async () => {
        const wrapper = mount(LoginComponent, {
            localVue,
            store: store({
                user: null,
                location: null,
                locations: null,
                settings: { data: { name: 'MPS' }, app: { name: 'MPS' }, demo: false },
            }),
            stubs: {
                RouterLink: RouterLinkStub,
            },
            mocks: { $route, $http: axios, $t: v => v, $Loading: { finish: v => v } },
        });

        // wrapper.setData({ login: { username: 'admin', password: '123456', remember: '' } });
        expect(wrapper.isVueInstance()).toBeTruthy();
        expect(wrapper.vm.login.username).toBe('admin');
        expect(wrapper.vm.login.password).toBe('123456');

        // wrapper.vm.signIn();
        // window.location.assign = jest.fn();
        // expect(window.location.href).not.toMatch(/login/);
    });
});
