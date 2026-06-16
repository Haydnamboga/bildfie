let _access: string | null = null;
let _refresh: string | null = null;

export const TokenStore = {
  get: () => _access,
  getRefresh: () => _refresh,
  set(access: string, refresh: string) {
    _access = access;
    _refresh = refresh;
  },
  clear() {
    _access = null;
    _refresh = null;
  },
};
