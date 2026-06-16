import { IsEmail, IsOptional, IsString, Length, MinLength } from "class-validator";

export class LoginDto {
  @IsEmail()
  email!: string;

  @IsString()
  @MinLength(1)
  password!: string;

  @IsOptional()
  @IsString()
  @Length(6, 6)
  mfaToken?: string;
}
