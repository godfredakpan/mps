import Vuex from 'vuex';
import mockAxios from 'axios';
import ViewUI from 'view-design';
import store from '@mpsjs/store/index';
// import flushPromises from 'flush-promises';
import { config, mount, createLocalVue, RouterLinkStub } from '@vue/test-utils';

config.silent = true;
config.stubs['Tooltip'] = '<div />';
config.methods['onSelect'] = () => {};
config.methods['onOpenChange'] = () => {};

import AppComponent from '@mpscom/AppComponent';
import VuePerfectScrollbar from 'vue-perfect-scrollbar';
import LoadingComponent from '@mpscom/helpers/LoadingComponent.vue';
import SelectLocationComponent from '@mpscom/helpers/SelectLocationComponent';

const localVue = createLocalVue();
localVue.use(Vuex);
localVue.use(ViewUI);
localVue.component('Loading', LoadingComponent);
localVue.component('vue-perfect-scrollbar', VuePerfectScrollbar);
localVue.component('select-location-component', SelectLocationComponent);

import Event from '@mpsjs/core/Event';
localVue.prototype.$event = Event;

import Filters from '@mpsjs/core/Filters';
for (const [key, value] of Object.entries(Filters)) {
    localVue.filter(key, value);
}

import VueRouter from 'vue-router';
localVue.use(VueRouter);
const router = new VueRouter();

describe('App', () => {
    it('should be working fine', async () => {
        const wrapper = mount(AppComponent, {
            localVue,
            router,
            store: store({
                user: null,
                location: null,
                locations: null,
                settings: { data: { name: 'MPS' }, app: { name: 'MPS' }, demo: false },
            }),
            mocks: {
                $route: { name: 'dashboard', path: '/', meta: { admin: false } },
                $http: mockAxios,
                $t: v => v,
                $tc: v => v,
                $Loading: { finish: v => v },
            },
        });

        // await flushPromises();
        window.location.assign = jest.fn();
        expect(wrapper.isVueInstance()).toBeTruthy();
        expect(window.location.href).toMatch(/login/);
    });
});
