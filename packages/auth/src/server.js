"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || (function () {
    var ownKeys = function(o) {
        ownKeys = Object.getOwnPropertyNames || function (o) {
            var ar = [];
            for (var k in o) if (Object.prototype.hasOwnProperty.call(o, k)) ar[ar.length] = k;
            return ar;
        };
        return ownKeys(o);
    };
    return function (mod) {
        if (mod && mod.__esModule) return mod;
        var result = {};
        if (mod != null) for (var k = ownKeys(mod), i = 0; i < k.length; i++) if (k[i] !== "default") __createBinding(result, mod, k[i]);
        __setModuleDefault(result, mod);
        return result;
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
exports.signAccessToken = signAccessToken;
exports.signRefreshToken = signRefreshToken;
exports.verifyAccessToken = verifyAccessToken;
exports.verifyRefreshToken = verifyRefreshToken;
exports.hashPassword = hashPassword;
exports.comparePassword = comparePassword;
exports.generateMfaSecret = generateMfaSecret;
exports.getMfaUri = getMfaUri;
exports.verifyMfaToken = verifyMfaToken;
const jwt = __importStar(require("jsonwebtoken"));
const bcrypt = __importStar(require("bcryptjs"));
const otplib_1 = require("otplib");
function jwtSecret() {
    const s = process.env.JWT_SECRET;
    if (!s)
        throw new Error("JWT_SECRET not set");
    return s;
}
function refreshSecret() {
    const s = process.env.REFRESH_TOKEN_SECRET;
    if (!s)
        throw new Error("REFRESH_TOKEN_SECRET not set");
    return s;
}
function signAccessToken(payload) {
    const { sub, ...rest } = payload;
    return jwt.sign(rest, jwtSecret(), {
        subject: sub,
        expiresIn: (process.env.JWT_EXPIRES_IN ?? "15m"),
    });
}
function signRefreshToken(sub) {
    return jwt.sign({}, refreshSecret(), {
        subject: sub,
        expiresIn: (process.env.REFRESH_TOKEN_EXPIRES_IN ?? "7d"),
    });
}
function verifyAccessToken(token) {
    const decoded = jwt.verify(token, jwtSecret());
    return {
        sub: decoded.sub,
        email: decoded.email,
        fullName: decoded.fullName,
        role: decoded.role,
        mfaVerified: decoded.mfaVerified,
    };
}
function verifyRefreshToken(token) {
    const decoded = jwt.verify(token, refreshSecret());
    return { sub: decoded.sub };
}
const BCRYPT_ROUNDS = 12;
function hashPassword(plain) {
    return bcrypt.hash(plain, BCRYPT_ROUNDS);
}
function comparePassword(plain, hash) {
    return bcrypt.compare(plain, hash);
}
function generateMfaSecret() {
    return otplib_1.authenticator.generateSecret();
}
function getMfaUri(secret, email) {
    return otplib_1.authenticator.keyuri(email, process.env.MFA_ISSUER ?? "bildfie", secret);
}
function verifyMfaToken(secret, token) {
    return otplib_1.authenticator.verify({ token, secret });
}
//# sourceMappingURL=server.js.map